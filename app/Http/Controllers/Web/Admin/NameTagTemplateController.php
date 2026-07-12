<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\NameTagTemplate;
use Illuminate\Http\Request;

class NameTagTemplateController extends Controller
{
    public function index()
    {
        $templates = NameTagTemplate::orderBy('is_system', 'desc')->orderBy('name')->get();
        return view('admin.nametags.template_index', compact('templates'));
    }

    public function edit(NameTagTemplate $template)
    {
        return view('admin.nametags.template_edit', compact('template'));
    }

    public function update(Request $request, NameTagTemplate $template)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'width'        => 'required|numeric|min:3|max:30',
            'height'       => 'required|numeric|min:3|max:30',
            'orientation'  => 'required|in:portrait,landscape',
            'html_content' => 'required|string',
        ]);

        $template->update($data);

        return redirect()->route('admin.nametag-templates.edit', $template)
            ->with('success', 'Template berhasil disimpan.');
    }

    public function duplicate(NameTagTemplate $template)
    {
        $new = $template->replicate();
        $new->slug        = $template->slug . '_copy_' . time();
        $new->name        = $template->name . ' (Salinan)';
        $new->is_system   = false;
        $new->save();

        return redirect()->route('admin.nametag-templates.edit', $new)
            ->with('success', 'Template berhasil diduplikasi.');
    }

    public function destroy(NameTagTemplate $template)
    {
        if ($template->is_system) {
            return back()->with('error', 'Template sistem tidak dapat dihapus.');
        }

        $template->delete();

        return redirect()->route('admin.nametag-templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    public function preview(Request $request, NameTagTemplate $template)
    {
        $html = $request->input('html_content', $template->html_content);
        $width  = $request->input('width',  $template->width);
        $height = $request->input('height', $template->height);

        $logoUrl     = asset('assets/img/logo.png');
        $cornerTrUrl = asset('assets/img/upper right corner.png');
        $cornerBlUrl = asset('assets/img/bottom left corner.png');

        $photoHtml = '<img src="https://ui-avatars.com/api/?name=Contoh+Siswa&size=200&background=1e3a5f&color=fff" '
                   . 'style="width:100%;height:100%;object-fit:cover;object-position:center top" alt="foto">';

        $qrRaw  = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->format('svg')->generate('0');
        $qrHtml = preg_replace('/(<svg[^>]+)\s+width="\d+"/', '$1 width="100%"', $qrRaw);
        $qrHtml = preg_replace('/(<svg[^>]+)\s+height="\d+"/', '$1 height="100%"', $qrHtml);

        $rendered = str_replace(
            ['{name}', '{kelas}', '{sekolah}', '{cabang}', '{photo_url}', '{photo_html}', '{qr_html}', '{logo_url}', '{corner_tr_url}', '{corner_bl_url}', '{width}', '{height}'],
            ['Contoh Nama Siswa', 'XII IPA 1', 'SMA Negeri 1 Gunungsitoli', 'Gunungsitoli', '', $photoHtml, $qrHtml, $logoUrl, $cornerTrUrl, $cornerBlUrl, $width, $height],
            $html
        );

        return response()->json(['html' => $rendered]);
    }
}
