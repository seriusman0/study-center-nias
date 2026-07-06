<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipJournal;
use App\Models\ScholarshipJournalAttachment;
use App\Models\ScholarshipJournalItem;
use Illuminate\Http\Request;

class ScholarshipJournalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $journals = $user->scholarshipJournals()->latest()->paginate(10);

        $stats = [
            'approved' => $user->scholarshipJournals()->where('status', 'approved')->count(),
            'pending' => $user->scholarshipJournals()->whereIn('status', ['submitted', 'under_review'])->count(),
            'draft' => $user->scholarshipJournals()->where('status', 'draft')->count(),
            'revision' => $user->scholarshipJournals()->where('status', 'revision_required')->count(),
        ];

        return view('scholarship-journal.index', compact('journals', 'stats'));
    }

    public function create()
    {
        return view('scholarship-journal.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2020|max:2030',
            'action' => 'required|in:draft,submit',
            'gpa_current_semester' => 'nullable|numeric|min:0|max:4',
            'cumulative_gpa' => 'nullable|numeric|min:0|max:4',
            'academic_summary' => 'nullable|string|max:2000',
            'class_attendance_percentage' => 'nullable|integer|min:0|max:100',
            'organization_activities' => 'nullable|string|max:2000',
            'training_seminars' => 'nullable|string|max:2000',
            'achievements' => 'nullable|string|max:2000',
            'community_service_details' => 'nullable|string|max:2000',
            'service_hours' => 'nullable|integer|min:0',
            'personal_reflection' => 'nullable|string|max:3000',
            'next_month_goals' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,docx',
            'attachment_types.*' => 'nullable|in:transkrip_khs,sertifikat,foto_kegiatan,lainnya',
        ]);

        $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $title = 'Jurnal Kegiatan - ' . $months[$data['period_month'] - 1] . ' ' . $data['period_year'];

        $journal = auth()->user()->scholarshipJournals()->create([
            'title' => $title,
            'period_month' => $data['period_month'],
            'period_year' => $data['period_year'],
            'status' => $data['action'] === 'submit' ? 'submitted' : 'draft',
            'submitted_at' => $data['action'] === 'submit' ? now() : null,
        ]);

        ScholarshipJournalItem::create(array_merge(
            ['journal_id' => $journal->id],
            collect($data)->only([
                'gpa_current_semester','cumulative_gpa','academic_summary','class_attendance_percentage',
                'organization_activities','training_seminars','achievements',
                'community_service_details','service_hours','personal_reflection','next_month_goals',
            ])->toArray()
        ));

        $this->storeAttachments($request, $journal->id, $data);

        $msg = $data['action'] === 'submit' ? 'Jurnal berhasil dikirim untuk review.' : 'Draft jurnal berhasil disimpan.';
        return redirect()->route('scholarship-journal.index')->with('success', $msg);
    }

    public function show($id)
    {
        $journal = auth()->user()->scholarshipJournals()
            ->with(['item', 'attachments', 'reviewer'])
            ->findOrFail($id);

        return view('scholarship-journal.show', compact('journal'));
    }

    public function edit($id)
    {
        $journal = auth()->user()->scholarshipJournals()
            ->with(['item', 'attachments'])
            ->findOrFail($id);

        abort_if(!$journal->isEditable(), 403, 'Jurnal tidak dapat diedit pada status ini.');

        return view('scholarship-journal.edit', compact('journal'));
    }

    public function update(Request $request, $id)
    {
        $journal = auth()->user()->scholarshipJournals()->findOrFail($id);
        abort_if(!$journal->isEditable(), 403);

        $data = $request->validate([
            'action' => 'required|in:draft,submit',
            'gpa_current_semester' => 'nullable|numeric|min:0|max:4',
            'cumulative_gpa' => 'nullable|numeric|min:0|max:4',
            'academic_summary' => 'nullable|string|max:2000',
            'class_attendance_percentage' => 'nullable|integer|min:0|max:100',
            'organization_activities' => 'nullable|string|max:2000',
            'training_seminars' => 'nullable|string|max:2000',
            'achievements' => 'nullable|string|max:2000',
            'community_service_details' => 'nullable|string|max:2000',
            'service_hours' => 'nullable|integer|min:0',
            'personal_reflection' => 'nullable|string|max:3000',
            'next_month_goals' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,docx',
            'attachment_types.*' => 'nullable|in:transkrip_khs,sertifikat,foto_kegiatan,lainnya',
        ]);

        $journal->update([
            'status' => $data['action'] === 'submit' ? 'submitted' : 'draft',
            'submitted_at' => $data['action'] === 'submit' ? now() : $journal->submitted_at,
            'reviewer_notes' => $data['action'] === 'submit' ? null : $journal->reviewer_notes,
        ]);

        $itemData = collect($data)->only([
            'gpa_current_semester','cumulative_gpa','academic_summary','class_attendance_percentage',
            'organization_activities','training_seminars','achievements',
            'community_service_details','service_hours','personal_reflection','next_month_goals',
        ])->toArray();

        $journal->item()->updateOrCreate(['journal_id' => $journal->id], $itemData);
        $this->storeAttachments($request, $journal->id, $data);

        $msg = $data['action'] === 'submit' ? 'Jurnal berhasil dikirim ulang.' : 'Draft berhasil diperbarui.';
        return redirect()->route('scholarship-journal.show', $journal->id)->with('success', $msg);
    }

    private function storeAttachments(Request $request, int $journalId, array $data): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $index => $file) {
            $path = $file->store('journal-attachments', 'public');
            ScholarshipJournalAttachment::create([
                'journal_id' => $journalId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $data['attachment_types'][$index] ?? 'lainnya',
            ]);
        }
    }
}
