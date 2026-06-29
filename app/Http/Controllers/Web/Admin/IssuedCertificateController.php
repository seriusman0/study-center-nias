<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\IssuedCertificate;
use App\Models\KelasMaster;
use App\Models\Role;
use App\Models\User;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IssuedCertificateController extends Controller
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

        $issued    = $query->paginate(20)->withQueryString();
        $templates = CertificateTemplate::where('is_active', true)->orderBy('nama')->get();
        $students  = User::whereHas('roles', fn($q) => $q->where('name', 'student'))
            ->where('is_active', true)
            ->with('cabang')
            ->orderBy('name')
            ->get();
        $kelasList = KelasMaster::orderBy('nama')->get();

        return view('admin.certificates.issued.index', compact('issued', 'templates', 'students', 'kelasList'));
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
            return redirect()->back()->with('errors', ['User yang dipilih bukan siswa.']);
        }

        try {
            $this->service->issue(
                $template,
                $student,
                $validated['nama_kursus'],
                Carbon::parse($validated['tanggal_lulus']),
                auth()->user(),
            );
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('errors', ['Gagal menerbitkan sertifikat: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.certificates.issued.index')
            ->with('success', 'Sertifikat berhasil diterbitkan.');
    }

    public function download(IssuedCertificate $cert)
    {
        if (! Storage::disk('public')->exists($cert->file_path)) {
            return redirect()->route('admin.certificates.issued.index')
                ->with('errors', ['File PDF tidak ditemukan. Silakan terbitkan ulang.']);
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

        return redirect()->route('admin.certificates.issued.index')
            ->with('success', 'Sertifikat berhasil dihapus.');
    }
}
