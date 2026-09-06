<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\JurnalOfflineTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JurnalOfflineTemplateApiController extends Controller
{
    /**
     * Display a paginated list of offline jurnal templates.
     */
    public function index(Request $request)
    {
        $templates = JurnalOfflineTemplate::with(['cabang', 'uploader'])
            ->when($request->filled('cabang_id'), fn ($q) => $q->where('cabang_id', $request->cabang_id))
            ->orderBy('cabang_id')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($templates);
    }

    /**
     * Store a new offline jurnal template (PDF).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'file'      => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('jurnal-offline-templates', 'local');

        $template = JurnalOfflineTemplate::create([
            'cabang_id'     => $validated['cabang_id'],
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'uploaded_by'   => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Template jurnal offline berhasil diupload.',
            'data'    => $template->load(['cabang', 'uploader']),
        ], 201);
    }

    /**
     * Download an offline jurnal template file.
     */
    public function download(JurnalOfflineTemplate $template)
    {
        if (!Storage::disk('local')->exists($template->path)) {
            return response()->json(['message' => 'File template tidak ditemukan.'], 404);
        }

        return Storage::disk('local')->download(
            $template->path,
            $template->original_name
        );
    }

    /**
     * Delete an offline jurnal template.
     */
    public function destroy(JurnalOfflineTemplate $template)
    {
        Storage::disk('local')->delete($template->path);
        $template->delete();

        return response()->json([
            'message' => 'Template jurnal offline berhasil dihapus.',
        ]);
    }
}
