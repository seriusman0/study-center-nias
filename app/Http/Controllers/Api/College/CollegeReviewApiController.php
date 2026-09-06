<?php

namespace App\Http\Controllers\Api\College;

use App\Http\Controllers\Controller;
use App\Models\CollegeProfile;
use App\Models\ScholarshipJournal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Self-service API untuk role college — dashboard review jurnal mahasiswa
 * dari institusi (kampus) yang sama.
 *
 * Mirrors JournalReviewController (web) tapi JSON untuk Android.
 */
class CollegeReviewApiController extends Controller
{
    /** List scholarship journals dari institusi college yang sama */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'status'  => 'nullable|in:draft,submitted,under_review,approved,revision_required',
            'q'       => 'nullable|string|max:100',
            'campus'  => 'nullable|string|max:255',
            'page'    => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $collegeProfile = CollegeProfile::where('user_id', $user->id)->first();
        $userInstitution = $collegeProfile?->institution_name;

        $query = ScholarshipJournal::with(['student.studentProfile'])
            ->whereHas('student.roles', fn($q) => $q->where('name', 'student'));

        if ($userInstitution) {
            $query->whereHas('student.studentProfile', fn($q) =>
                $q->where('campus_name', $userInstitution)
            );
        }

        if (isset($data['campus']) && $data['campus']) {
            $query->whereHas('student.studentProfile', fn($q) =>
                $q->where('campus_name', $data['campus'])
            );
        }

        if (isset($data['status']) && $data['status']) {
            $query->where('status', $data['status']);
        }

        if (isset($data['q']) && $data['q']) {
            $term = '%' . $data['q'] . '%';
            $query->where(function ($w) use ($term) {
                $w->where('title', 'like', $term)
                  ->orWhereHas('student', fn($s) =>
                      $s->where('name', 'like', $term)->orWhere('username', 'like', $term)
                  );
            });
        }

        $perPage = (int) ($data['per_page'] ?? 20);
        $journals = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $journals->items() === null ? [] : array_map(fn($j) => $this->transformSummary($j), $journals->items()),
            'meta' => [
                'current_page' => $journals->currentPage(),
                'last_page'    => $journals->lastPage(),
                'per_page'     => $journals->perPage(),
                'total'        => $journals->total(),
            ],
        ]);
    }

    /** Detail: journal + item + attachments */
    public function show(Request $request, ScholarshipJournal $journal): JsonResponse
    {
        $user = $request->user();

        // College: enforce institution scope
        if ($user->hasRole('college')) {
            $collegeProfile = CollegeProfile::where('user_id', $user->id)->first();
            $campus = $journal->student->studentProfile?->campus_name;
            if ($collegeProfile && $campus !== $collegeProfile->institution_name) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $journal->load(['student.studentProfile', 'item', 'attachments', 'reviewer']);

        return response()->json([
            'id'             => $journal->id,
            'title'          => $journal->title,
            'period_month'   => $journal->period_month,
            'period_year'    => $journal->period_year,
            'status'         => $journal->status,
            'status_label'   => $journal->statusLabel,
            'submitted_at'   => $journal->submitted_at?->toDateTimeString(),
            'reviewer_notes' => $journal->reviewer_notes,
            'student' => [
                'id'       => $journal->student->id,
                'name'     => $journal->student->name,
                'username' => $journal->student->username,
                'avatar'   => $journal->student->avatar,
                'campus'   => $journal->student->studentProfile?->campus_name,
                'semester' => $journal->student->studentProfile?->current_semester,
            ],
            'item' => $journal->item ? [
                'gpa_current_semester'     => $journal->item->gpa_current_semester,
                'cumulative_gpa'           => $journal->item->cumulative_gpa,
                'class_attendance_percentage' => $journal->item->class_attendance_percentage,
                'academic_summary'        => $journal->item->academic_summary,
                'organization_activities' => $journal->item->organization_activities,
                'training_seminars'       => $journal->item->training_seminars,
                'achievements'            => $journal->item->achievements,
                'community_service_details' => $journal->item->community_service_details,
                'service_hours'           => $journal->item->service_hours,
                'personal_reflection'     => $journal->item->personal_reflection,
                'next_month_goals'        => $journal->item->next_month_goals,
            ] : null,
            'attachments' => $journal->attachments->map(fn($a) => [
                'id'   => $a->id,
                'url'  => $a->url ?? asset('storage/' . $a->path),
                'name' => $a->name ?? $a->file_name,
            ])->values(),
            'reviewer' => $journal->reviewer ? [
                'id'   => $journal->reviewer->id,
                'name' => $journal->reviewer->name,
            ] : null,
            'can_review' => in_array($journal->status, ['submitted', 'under_review']),
        ]);
    }

    /** Submit review */
    public function review(Request $request, ScholarshipJournal $journal): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('college')) {
            $collegeProfile = CollegeProfile::where('user_id', $user->id)->first();
            $campus = $journal->student->studentProfile?->campus_name;
            if ($collegeProfile && $campus !== $collegeProfile->institution_name) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        if (!in_array($journal->status, ['submitted', 'under_review'])) {
            return response()->json(['ok' => false, 'message' => 'Jurnal tidak dalam status yang bisa diverifikasi.'], 422);
        }

        $data = $request->validate([
            'action'         => 'required|in:approved,revision_required',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($journal, $user, $data) {
            $journal->update([
                'status'         => $data['action'],
                'reviewed_by'    => $user->id,
                'reviewed_at'    => now(),
                'reviewer_notes' => $data['reviewer_notes'] ?? null,
            ]);
        });

        return response()->json([
            'ok'      => true,
            'message' => $data['action'] === 'approved' ? 'Jurnal disetujui.' : 'Jurnal dikembalikan untuk revisi.',
            'status'  => $journal->fresh()->status,
        ]);
    }

    // ── Internal helpers ────────────────────────────────────────────────────
    private function transformSummary(ScholarshipJournal $journal): array
    {
        return [
            'id'          => $journal->id,
            'title'       => $journal->title,
            'student'     => $journal->student?->name ?? '',
            'campus'      => $journal->student?->studentProfile?->campus_name ?? '',
            'period_month' => $journal->period_month,
            'period_year'  => $journal->period_year,
            'status'      => $journal->status,
            'status_label' => $journal->statusLabel,
            'submitted_at' => $journal->submitted_at?->format('d M Y'),
        ];
    }
}
