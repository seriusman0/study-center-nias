<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\CertificateTemplate;
use App\Models\User;
use App\Services\CertificateService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateApiController extends Controller
{
    public function __construct(private CertificateService $service) {}

    public function index()
    {
        $templates = CertificateTemplate::withTrashed()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:150',
            'deskripsi'    => 'nullable|string',
            'html_content' => 'required|string',
            'orientation'  => 'required|in:portrait,landscape',
            'paper_size'   => 'required|in:a4',
            'is_active'    => 'boolean',
            'logo'         => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $validated['html_content'] = $this->service->sanitizeTemplate($validated['html_content']);
        $validated['created_by']   = auth()->id();
        $validated['is_active']    = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('certificates/logos', 'public');
        }

        unset($validated['logo']);
        $template = CertificateTemplate::create($validated);

        return response()->json([
            'message' => 'Template sertifikat berhasil dibuat.',
            'data'    => $template
        ], 201);
    }

    public function update(Request $request, CertificateTemplate $template)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:150',
            'deskripsi'    => 'nullable|string',
            'html_content' => 'required|string',
            'orientation'  => 'required|in:portrait,landscape',
            'paper_size'   => 'required|in:a4',
            'is_active'    => 'boolean',
            'logo'         => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $validated['html_content'] = $this->service->sanitizeTemplate($validated['html_content']);
        $validated['is_active']    = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            if ($template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('certificates/logos', 'public');
        }

        unset($validated['logo']);
        $template->update($validated);

        return response()->json([
            'message' => 'Template sertifikat berhasil diperbarui.',
            'data'    => $template
        ]);
    }

    public function destroy(CertificateTemplate $template)
    {
        if ($template->issuedCertificates()->exists()) {
            return response()->json([
                'message' => 'Template tidak dapat dihapus karena sudah digunakan untuk menerbitkan sertifikat.'
            ], 422);
        }

        $template->delete();

        return response()->json([
            'message' => 'Template sertifikat berhasil dihapus.'
        ]);
    }

    public function previewSaved(CertificateTemplate $template)
    {
        $dummyStudent = new User([
            'name'      => 'Nama Peserta Contoh',
            'avatar'    => null,
            'cabang_id' => null,
        ]);
        $dummyCabang = new Cabang(['nama' => 'Cabang Contoh']);
        $dummyStudent->setRelation('cabang', $dummyCabang);

        $logoBase64 = $this->service->logoToBase64($template->logo_path);

        $compiled = $this->service->interpolate(
            $template->html_content,
            $dummyStudent,
            'Nama Kursus Contoh',
            Carbon::now(),
            'SCN-' . now()->year . '-CNT-PREVIEW000',
            $logoBase64,
            '',
        );

        $pdf = Pdf::loadView('pdf.certificate', [
            'compiledHtml' => $compiled,
            'orientation'  => $template->orientation,
        ])
        ->setOptions([
            'enable_remote'        => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'dpi'                  => 150,
        ])
        ->setPaper($template->paper_size, $template->orientation);

        return $pdf->stream("preview-{$template->nama}.pdf");
    }
}
