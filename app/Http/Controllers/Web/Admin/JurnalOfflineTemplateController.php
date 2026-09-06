<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\JurnalOfflineTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JurnalOfflineTemplateController extends Controller
{
    public function index()
    {
        $cabangs   = Cabang::orderBy('nama')->get();
        $templates = JurnalOfflineTemplate::with('cabang', 'uploader')
            ->orderBy('cabang_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('cabang_id');

        return view('admin.jurnal.offline-template', compact('cabangs', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'file'      => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('jurnal-offline-templates', 'local');

        JurnalOfflineTemplate::create([
            'cabang_id'     => $request->cabang_id,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'uploaded_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Template berhasil diupload.');
    }

    public function download(JurnalOfflineTemplate $template)
    {
        abort_unless(Storage::disk('local')->exists($template->path), 404);

        return Storage::disk('local')->download($template->path, $template->original_name);
    }

    public function destroy(JurnalOfflineTemplate $template)
    {
        Storage::disk('local')->delete($template->path);
        $template->delete();

        return back()->with('success', 'Template berhasil dihapus.');
    }
}
