<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\User;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IssuedCertificateApiController extends Controller
{
    public function __construct(private CertificateService $service) {}

    public function index(Request $request)
    {
        $query = IssuedCertificate::with(['student', 'template', 'issuer'])
            ->orderByDesc('issued_at');

        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        $issued = $query->paginate(20);
        return response()->json($issued);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'template_id'  => 'required|exists:certificate_templates,id',
            'nama_kursus'  => 'required|string|max:150',
            'tanggal_lulus'=> 'required|date|before_or_equal:today',
        ]);

        $student  = User::with('cabang')->findOrFail($validated['user_id']);
        $template = CertificateTemplate::where('is_active', true)->findOrFail($validated['template_id']);

        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'User yang dipilih bukan siswa.'], 422);
        }

        try {
            $certificate = $this->service->issue(
                $template,
                $student,
                $validated['nama_kursus'],
                Carbon::parse($validated['tanggal_lulus']),
                auth()->user(),
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menerbitkan sertifikat: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Sertifikat berhasil diterbitkan.',
            'data'    => $certificate
        ], 201);
    }

    public function download(IssuedCertificate $cert)
    {
        if (! Storage::disk('public')->exists($cert->file_path)) {
            return response()->json(['message' => 'File PDF tidak ditemukan.'], 404);
        }

        return Storage::disk('public')->download(
            $cert->file_path,
            $cert->nomor_sertifikat . '.pdf'
        );
    }

    public function destroy(IssuedCertificate $cert)
    {
        Storage::disk('public')->delete($cert->file_path);
        $cert->delete();

        return response()->json(['message' => 'Sertifikat berhasil dihapus.']);
    }
}
