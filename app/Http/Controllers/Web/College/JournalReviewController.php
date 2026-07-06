<?php

namespace App\Http\Controllers\Web\College;

use App\Http\Controllers\Controller;
use App\Models\ScholarshipJournal;
use App\Models\User;
use Illuminate\Http\Request;

class JournalReviewController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $collegeProfile = $user->collegeProfile;

        $query = ScholarshipJournal::with(['student.studentProfile'])
            ->whereHas('student.roles', fn($q) => $q->where('name', 'student'));

        // College role: scope to own institution
        if ($user->hasRole('college') && $collegeProfile) {
            $query->whereHas('student.studentProfile', fn($q) =>
                $q->where('campus_name', $collegeProfile->institution_name)
            );
        }

        // Filters
        if ($request->filled('campus')) {
            $query->whereHas('student.studentProfile', fn($q) =>
                $q->where('campus_name', $request->campus)
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $journals = $query->latest()->paginate(20);

        $campuses = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->whereHas('studentProfile', fn($q) => $q->whereNotNull('campus_name'))
            ->join('student_profiles', 'users.id', '=', 'student_profiles.user_id')
            ->distinct()
            ->pluck('student_profiles.campus_name');

        return view('college.dashboard', compact('journals', 'campuses'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $journal = ScholarshipJournal::with(['student.studentProfile', 'item', 'attachments', 'reviewer'])
            ->findOrFail($id);

        // College can only review journals from their institution
        if ($user->hasRole('college') && $user->collegeProfile) {
            $campus = $journal->student->studentProfile?->campus_name;
            abort_if($campus !== $user->collegeProfile->institution_name, 403);
        }

        return view('college.journal-review', compact('journal'));
    }

    public function review(Request $request, $id)
    {
        $user = auth()->user();
        $journal = ScholarshipJournal::findOrFail($id);

        // College: enforce institution scope
        if ($user->hasRole('college') && $user->collegeProfile) {
            $campus = $journal->student->studentProfile?->campus_name;
            abort_if($campus !== $user->collegeProfile->institution_name, 403);
        }

        $data = $request->validate([
            'action' => 'required|in:approved,revision_required',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        abort_if(!in_array($journal->status, ['submitted', 'under_review']), 422, 'Jurnal tidak dalam status yang bisa diverifikasi.');

        $journal->update([
            'status' => $data['action'],
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'reviewer_notes' => $data['reviewer_notes'] ?? null,
        ]);

        $msg = $data['action'] === 'approved' ? 'Jurnal disetujui.' : 'Jurnal dikembalikan untuk revisi.';
        return redirect()->route('college.dashboard')->with('success', $msg);
    }
}
