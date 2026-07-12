<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\NameTagTemplate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NameTagController extends Controller
{
    public function index(Request $request)
    {
        $studentRoleId = Role::where('name', 'student')->value('id');

        $query = User::with(['studentProfile', 'cabang', 'roles'])
            ->where('is_active', true);

        if ($studentRoleId) {
            $query->whereHas('roles', fn($q) => $q->where('roles.id', $studentRoleId));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(fn($w) => $w->where('name', 'like', $term)
                ->orWhere('username', 'like', $term)
                ->orWhereHas('studentProfile', fn($s) => $s->where('school_name', 'like', $term)
                    ->orWhere('grade_class', 'like', $term)));
        }

        if ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        $students  = $query->orderBy('name')->paginate(50)->withQueryString();
        $cabangs   = Cabang::orderBy('nama')->get();
        $templates = NameTagTemplate::orderBy('is_system', 'desc')->orderBy('name')->get();

        return view('admin.nametags.index', compact('students', 'cabangs', 'templates'));
    }

    public function generate(Request $request)
    {
        $slugs = NameTagTemplate::pluck('slug')->implode(',');

        $data = $request->validate([
            'user_ids'    => 'required|array|min:1',
            'user_ids.*'  => 'integer|exists:users,id',
            'template'    => 'nullable|string',
            'width_cm'    => 'nullable|numeric|min:3|max:30',
            'height_cm'   => 'nullable|numeric|min:3|max:30',
            'auto_print'  => 'nullable|boolean',
        ]);

        $tplSlug  = $data['template'] ?? 'standard';
        $template = NameTagTemplate::where('slug', $tplSlug)->firstOrFail();

        $width    = (float) ($data['width_cm']  ?? $template->width);
        $height   = (float) ($data['height_cm'] ?? $template->height);
        $autoPrint   = (bool) ($request->boolean('auto_print'));
        $logoUrl     = asset('assets/img/logo.png');
        $cornerTrUrl = asset('assets/img/upper right corner.png');
        $cornerBlUrl = asset('assets/img/bottom left corner.png');

        $students = User::with(['studentProfile', 'cabang'])
            ->whereIn('id', $data['user_ids'])
            ->orderBy('name')
            ->get();

        // Pre-render each card
        $cards = $students->map(function ($s) use ($template, $width, $height, $logoUrl, $cornerTrUrl, $cornerBlUrl) {
            $sp = $s->studentProfile;

            if ($sp?->photo) {
                $photoHtml = '<img src="' . asset('storage/' . $sp->photo) . '" alt="' . e($s->name) . '" '
                           . 'style="width:100%;height:100%;object-fit:cover;object-position:center top">';
            } else {
                $photoHtml = '<div class="photo-placeholder"><span>Tempel<br>Foto</span></div>';
            }

            $qrRaw  = (string) QrCode::size(200)->format('svg')->generate((string) $s->id);
            // Replace fixed px dimensions with 100% so SVG fills its container
            $qrHtml = preg_replace('/(<svg[^>]+)\s+width="\d+"/', '$1 width="100%"', $qrRaw);
            $qrHtml = preg_replace('/(<svg[^>]+)\s+height="\d+"/', '$1 height="100%"', $qrHtml);

            return $template->render([
                'name'       => $s->name,
                'kelas'      => $sp?->grade_class ?? '',
                'sekolah'    => $sp?->school_name ?? '',
                'cabang'     => $s->cabang?->nama ?? '',
                'photo_url'  => $sp?->photo ? asset('storage/' . $sp->photo) : '',
                'photo_html' => $photoHtml,
                'qr_html'    => $qrHtml,
                'logo_url'      => $logoUrl,
                'corner_tr_url' => $cornerTrUrl,
                'corner_bl_url' => $cornerBlUrl,
                'width'         => $width,
                'height'        => $height,
            ]);
        });

        return view('admin.nametags.generate', [
            'students'    => $students,
            'cards'       => $cards,
            'template'    => $template,
            'orientation' => $template->orientation,
            'width'       => $width,
            'height'      => $height,
            'autoPrint'   => $autoPrint,
        ]);
    }
}
