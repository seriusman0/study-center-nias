<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KelasMaster;
use App\Models\Presensi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = Presensi::with(['mentor:id,name', 'cabang:id,nama', 'kelasMaster:id,nama'])
            ->withCount('students')->latest('tanggal');

        if (! $user->isAdmin()) $q->where('mentor_id', $user->id);
        if ($request->filled('cabang_id')) $q->where('cabang_id', $request->cabang_id);
        if ($request->filled('from'))      $q->whereDate('tanggal', '>=', $request->from);
        if ($request->filled('to'))        $q->whereDate('tanggal', '<=', $request->to);

        $page = $q->paginate(20);
        return response()->json([
            'data' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    public function show(Request $request, Presensi $presensi): JsonResponse
    {
        $this->authorize($request, $presensi);
        $presensi->load(['mentor:id,name', 'cabang:id,nama', 'kelasMaster:id,nama', 'students:id,name,username']);
        return response()->json(['data' => $presensi]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateData($request);
        $presensi = DB::transaction(function () use ($data, $request) {
            $kelas = KelasMaster::find($data['kelas_id']);
            $payload = collect($data)->except(['student_ids', 'student_status'])->all();
            $payload['created_by'] = $request->user()->id;
            $payload['kelas'] = $kelas?->nama ?? '-';

            $presensi = Presensi::create($payload);
            $this->syncStudents($presensi, $data['student_ids'] ?? [], $data['student_status'] ?? []);
            return $presensi;
        });
        return response()->json(['data' => $presensi->load('mentor:id,name', 'kelasMaster:id,nama', 'students:id,name')], 201);
    }

    public function update(Request $request, Presensi $presensi): JsonResponse
    {
        $this->authorize($request, $presensi);
        $data = $this->validateData($request, $presensi);

        DB::transaction(function () use ($data, $presensi) {
            $kelas = KelasMaster::find($data['kelas_id']);
            $payload = collect($data)->except(['student_ids', 'student_status'])->all();
            $payload['kelas'] = $kelas?->nama ?? $presensi->kelas;
            $presensi->update($payload);
            $this->syncStudents($presensi, $data['student_ids'] ?? [], $data['student_status'] ?? []);
        });
        return response()->json(['data' => $presensi->fresh()->load('mentor:id,name', 'kelasMaster:id,nama', 'students:id,name')]);
    }

    public function destroy(Request $request, Presensi $presensi): JsonResponse
    {
        $this->authorize($request, $presensi);
        $presensi->delete();
        return response()->json(['message' => 'deleted']);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $studentRoleId = Role::where('name', 'student')->value('id');
        $user = $request->user();
        $q = User::with(['studentProfile:id,user_id,grade_class,school_name', 'cabang:id,nama'])
            ->where('is_active', true)
            ->whereHas('roles', fn($r) => $r->where('roles.id', $studentRoleId))
            ->select('id', 'name', 'username', 'cabang_id');

        if (! $user->isAdmin()) {
            if ($user->cabang_id) $q->where('cabang_id', $user->cabang_id);
            else return response()->json(['data' => []]);
        }
        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $q->where(fn($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
        }
        return response()->json([
            'data' => $q->orderBy('name')->limit(50)->get()->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'kelas' => $u->studentProfile?->grade_class,
                'cabang'=> $u->cabang?->nama,
            ]),
        ]);
    }

    private function authorize(Request $request, Presensi $presensi): void
    {
        $user = $request->user();
        if (! $user->isAdmin() && $presensi->mentor_id !== $user->id) abort(403);
    }

    private function validateData(Request $request, ?Presensi $presensi = null): array
    {
        return $request->validate([
            'mentor_id'        => ['required', Rule::exists('users', 'id')],
            'cabang_id'        => 'nullable|exists:cabangs,id',
            'kelas_id'         => ['required', Rule::exists('kelas_master', 'id')->whereNull('deleted_at')],
            'tanggal'          => 'required|date',
            'jam_mulai'        => 'required|date_format:H:i',
            'jam_selesai'      => 'required|date_format:H:i|after:jam_mulai',
            'materi'           => 'required|string|max:5000',
            'student_ids'      => 'required|array|min:1',
            'student_ids.*'    => 'integer|exists:users,id',
            'student_status'   => 'nullable|array',
            'student_status.*' => 'in:hadir,izin,sakit,alpha',
        ]);
    }

    private function syncStudents(Presensi $presensi, array $ids, array $statuses): void
    {
        $sync = [];
        foreach ($ids as $id) {
            $sync[$id] = ['status' => $statuses[$id] ?? 'hadir', 'keterangan' => null];
        }
        $presensi->students()->sync($sync);
    }
}
