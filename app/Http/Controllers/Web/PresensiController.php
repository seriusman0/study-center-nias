<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\KelasMaster;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Presensi::with(['mentor:id,name', 'cabang:id,nama'])
            ->withCount('students');

        if (! $user->isAdmin()) {
            $query->where('mentor_id', $user->id);
        }

        if ($request->filled('mentor_id')) {
            $query->where('mentor_id', $request->mentor_id);
        }
        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($w) => $w->where('kelas', 'like', $term)
                ->orWhere('materi', 'like', $term));
        }

        $presensi = $query->latest('tanggal')->latest('jam_mulai')->paginate(20)->withQueryString();
        $mentors = $this->mentorList();
        $cabangs = Cabang::orderBy('nama')->get();

        return view('presensi.index', compact('presensi', 'mentors', 'cabangs'));
    }

    public function create(Request $request)
    {
        $mentors = $this->mentorList();
        $cabangs = Cabang::orderBy('nama')->get();
        $defaultMentorId = $request->user()->isAdmin() ? null : $request->user()->id;

        return view('presensi.form', [
            'mentors'         => $mentors,
            'cabangs'         => $cabangs,
            'defaultMentorId' => $defaultMentorId,
            'presensi'        => null,
            'selectedStudents' => $this->selectedStudentsFromOld(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $presensi = DB::transaction(function () use ($data, $request) {
            $payload = collect($data)->except(['student_ids', 'student_status', 'foto'])->all();
            $payload['created_by'] = $request->user()->id;
            $payload['kelas'] = $this->resolveKelasLabel($payload['kelas_id'] ?? null, $payload['kelas'] ?? null);

            if ($request->hasFile('foto')) {
                $payload['foto'] = $request->file('foto')->store('presensi', 'public');
            }

            $presensi = Presensi::create($payload);

            $this->syncStudents($presensi, $data['student_ids'] ?? [], $data['student_status'] ?? []);

            return $presensi;
        });

        return redirect()->route('presensi.show', $presensi->id)
            ->with('success', 'Presensi disimpan.');
    }

    public function show(Presensi $presensi, Request $request)
    {
        $this->authorizeAccess($request, $presensi);

        $presensi->load(['mentor', 'cabang', 'creator', 'students.studentProfile', 'students.cabang']);

        return view('presensi.show', compact('presensi'));
    }

    public function edit(Presensi $presensi, Request $request)
    {
        $this->authorizeAccess($request, $presensi);

        $presensi->load(['students.studentProfile', 'students.cabang']);

        $mentors = $this->mentorList();
        $cabangs = Cabang::orderBy('nama')->get();

        $selectedStudents = $this->selectedStudentsFromOld();
        if ($selectedStudents->isEmpty()) {
            $selectedStudents = $presensi->students->map(function ($s) {
                return [
                    'id'     => $s->id,
                    'name'   => $s->name,
                    'kelas'  => $s->studentProfile?->grade_class,
                    'school' => $s->studentProfile?->school_name,
                    'cabang' => $s->cabang?->nama,
                    'status' => $s->pivot->status,
                ];
            });
        }

        return view('presensi.form', [
            'mentors'           => $mentors,
            'cabangs'           => $cabangs,
            'defaultMentorId'   => $presensi->mentor_id,
            'presensi'          => $presensi,
            'selectedStudents'  => $selectedStudents,
        ]);
    }

    private function selectedStudentsFromOld()
    {
        $oldIds = (array) old('student_ids', []);
        $oldStatus = (array) old('student_status', []);
        if (empty($oldIds)) {
            return collect();
        }
        return User::with(['studentProfile:id,user_id,grade_class,school_name', 'cabang:id,nama'])
            ->whereIn('id', $oldIds)
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'kelas'  => $u->studentProfile?->grade_class,
                'school' => $u->studentProfile?->school_name,
                'cabang' => $u->cabang?->nama,
                'status' => $oldStatus[$u->id] ?? 'hadir',
            ]);
    }

    public function update(Request $request, Presensi $presensi)
    {
        $this->authorizeAccess($request, $presensi);

        $data = $this->validateData($request, $presensi);

        DB::transaction(function () use ($data, $request, $presensi) {
            $payload = collect($data)->except(['student_ids', 'student_status', 'foto'])->all();
            $payload['kelas'] = $this->resolveKelasLabel($payload['kelas_id'] ?? null, $payload['kelas'] ?? null);

            if ($request->hasFile('foto')) {
                if ($presensi->foto) {
                    Storage::disk('public')->delete($presensi->foto);
                }
                $payload['foto'] = $request->file('foto')->store('presensi', 'public');
            }

            $presensi->update($payload);

            $this->syncStudents($presensi, $data['student_ids'] ?? [], $data['student_status'] ?? []);
        });

        return redirect()->route('presensi.show', $presensi->id)->with('success', 'Presensi diperbarui.');
    }

    public function destroy(Request $request, Presensi $presensi)
    {
        $this->authorizeAccess($request, $presensi);

        if ($presensi->foto) {
            Storage::disk('public')->delete($presensi->foto);
        }
        $presensi->delete();

        return redirect()->route('presensi.index')->with('success', 'Presensi dihapus.');
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $studentRoleId = Role::where('name', 'student')->value('id');
        $user = $request->user();

        $query = User::with(['studentProfile:id,user_id,grade_class,school_name', 'cabang:id,nama'])
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('roles.id', $studentRoleId))
            ->select('id', 'name', 'username', 'cabang_id');

        if (! $user->isAdmin()) {
            if ($user->cabang_id) {
                $query->where('cabang_id', $user->cabang_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($w) => $w->where('name', 'like', $term)
                ->orWhere('username', 'like', $term)
                ->orWhereHas('studentProfile', fn($s) => $s->where('school_name', 'like', $term)
                    ->orWhere('grade_class', 'like', $term)));
        }

        $students = $query->orderBy('name')->limit(50)->get()->map(function ($u) {
            return [
                'id'     => $u->id,
                'name'   => $u->name,
                'kelas'  => $u->studentProfile?->grade_class,
                'school' => $u->studentProfile?->school_name,
                'cabang' => $u->cabang?->nama,
            ];
        });

        return response()->json(['data' => $students]);
    }

    private function mentorList()
    {
        $mentorRoleId = Role::where('name', 'mentor')->value('id');
        if (! $mentorRoleId) {
            return collect();
        }

        return User::where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('roles.id', $mentorRoleId))
            ->orderBy('name')
            ->get(['id', 'name', 'username']);
    }

    private function validateData(Request $request, ?Presensi $presensi = null): array
    {
        return $request->validate([
            'mentor_id'        => ['required', Rule::exists('users', 'id')],
            'cabang_id'        => 'nullable|exists:cabangs,id',
            'kelas_id'         => ['required', Rule::exists('kelas_master', 'id')->whereNull('deleted_at')],
            'kelas'            => 'nullable|string|max:100',
            'tanggal'          => 'required|date',
            'jam_mulai'        => 'required|date_format:H:i',
            'jam_selesai'      => 'required|date_format:H:i|after:jam_mulai',
            'materi'           => 'required|string|max:5000',
            'foto'             => 'nullable|image|max:4096',
            'student_ids'      => 'required|array|min:1',
            'student_ids.*'    => 'integer|exists:users,id',
            'student_status'   => 'nullable|array',
            'student_status.*' => 'in:hadir,izin,sakit,alpha',
        ]);
    }

    private function resolveKelasLabel(?int $kelasId, ?string $fallback): ?string
    {
        if ($kelasId) {
            $nama = KelasMaster::where('id', $kelasId)->value('nama');
            if ($nama) return $nama;
        }
        return $fallback;
    }

    private function syncStudents(Presensi $presensi, array $ids, array $statuses): void
    {
        $sync = [];
        foreach ($ids as $id) {
            $sync[$id] = [
                'status'      => $statuses[$id] ?? 'hadir',
                'keterangan'  => null,
            ];
        }
        $presensi->students()->sync($sync);
    }

    private function authorizeAccess(Request $request, Presensi $presensi): void
    {
        $user = $request->user();
        if (! $user->isAdmin() && $presensi->mentor_id !== $user->id) {
            abort(403);
        }
    }
}
