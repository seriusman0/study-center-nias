<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KelasMaster;
use App\Models\MentorPresensi;
use App\Support\JurnalWeek;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MentorPresensiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $records = MentorPresensi::with(['kelas:id,nama,cabang_id', 'cabang:id,nama'])
            ->forMentor($user->id)
            ->latest('tanggal')
            ->latest('jam_datang')
            ->paginate(20);

        return view('mentor.presensi.index', compact('records'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        return view('mentor.presensi.form', [
            'presensi' => null,
            'cabangId' => $user->cabang_id,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if(! $user->cabang_id, 422, 'Akun Anda belum terkait cabang.');

        $data = $this->validateData($request, $user);

        MentorPresensi::create([
            'mentor_id'    => $user->id,
            'cabang_id'    => $user->cabang_id,
            'kelas_id'     => $data['kelas_id'],
            'tanggal'      => $data['tanggal'],
            'jam_datang'   => $data['jam_datang'],
            'jam_pulang'   => $data['jam_pulang'],
            'jumlah_murid' => $data['jumlah_murid'],
            'catatan'      => $data['catatan'] ?? null,
        ]);

        return redirect()->route('mentor-presensi.index')->with('success', 'Presensi tersimpan.');
    }

    public function edit(Request $request, MentorPresensi $presensi)
    {
        $this->authorizeOwn($request, $presensi);
        abort_if(! $presensi->canEdit(), 422, 'Presensi tidak dapat diedit (>24 jam). Hubungi admin.');

        $presensi->load('kelas:id,nama,cabang_id');

        return view('mentor.presensi.form', [
            'presensi' => $presensi,
            'cabangId' => $request->user()->cabang_id,
        ]);
    }

    public function update(Request $request, MentorPresensi $presensi)
    {
        $this->authorizeOwn($request, $presensi);
        abort_if(! $presensi->canEdit(), 422, 'Presensi tidak dapat diedit (>24 jam).');

        $data = $this->validateData($request, $request->user());

        $presensi->update([
            'kelas_id'     => $data['kelas_id'],
            'tanggal'      => $data['tanggal'],
            'jam_datang'   => $data['jam_datang'],
            'jam_pulang'   => $data['jam_pulang'],
            'jumlah_murid' => $data['jumlah_murid'],
            'catatan'      => $data['catatan'] ?? null,
        ]);

        return redirect()->route('mentor-presensi.index')->with('success', 'Presensi diperbarui.');
    }

    public function destroy(Request $request, MentorPresensi $presensi)
    {
        $this->authorizeOwn($request, $presensi);
        abort_if(! $presensi->canEdit(), 422, 'Presensi tidak dapat dihapus (>24 jam).');

        $presensi->delete();
        return redirect()->route('mentor-presensi.index')->with('success', 'Presensi dihapus.');
    }

    public function searchKelas(Request $request): JsonResponse
    {
        $user = $request->user();
        $cabangId = $user->isAdmin() && $request->filled('cabang_id')
            ? (int) $request->cabang_id
            : $user->cabang_id;

        $q = KelasMaster::active()
            ->with('cabang:id,nama')
            ->orderBy('nama');

        if ($cabangId) {
            $q->where('cabang_id', $cabangId);
        } elseif (! $user->isAdmin()) {
            return response()->json(['data' => []]);
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $q->where('nama', 'like', $term);
        }

        $kelas = $q->limit(50)->get()->map(fn($k) => [
            'id'     => $k->id,
            'nama'   => $k->nama,
            'cabang' => $k->cabang?->nama,
            'label'  => $k->cabang ? "{$k->nama} — {$k->cabang->nama}" : $k->nama,
        ]);

        return response()->json(['data' => $kelas]);
    }

    private function authorizeOwn(Request $request, MentorPresensi $presensi): void
    {
        abort_if($presensi->mentor_id !== $request->user()->id, 403);
    }

    private function validateData(Request $request, $user): array
    {
        $today = JurnalWeek::today()->toDateString();

        return $request->validate([
            'kelas_id' => [
                'required',
                Rule::exists('kelas_master', 'id')
                    ->where('is_active', true)
                    ->where('cabang_id', $user->cabang_id)
                    ->whereNull('deleted_at'),
            ],
            'tanggal'      => ['required', 'date', 'before_or_equal:' . $today],
            'jam_datang'   => 'required|date_format:H:i',
            'jam_pulang'   => 'required|date_format:H:i|after:jam_datang',
            'jumlah_murid' => 'required|integer|min:0|max:500',
            'catatan'      => 'nullable|string|max:1000',
        ]);
    }
}
