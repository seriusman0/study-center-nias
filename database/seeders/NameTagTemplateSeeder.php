<?php

namespace Database\Seeders;

use App\Models\NameTagTemplate;
use Illuminate\Database\Seeder;

class NameTagTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $cornerTR = '<img src="{corner_tr_url}" style="width:100%;height:100%;display:block" alt="">';
        $cornerBL = '<img src="{corner_bl_url}" style="width:100%;height:100%;display:block" alt="">';

        $templates = [
            [
                'slug'        => 'standard',
                'name'        => 'Standard',
                'description' => 'Teks nama, kelas, sekolah. Tanpa foto.',
                'width'       => 8.5,
                'height'      => 5.5,
                'orientation' => 'portrait',
                'is_system'   => true,
                'html_content' => <<<'HTML'
<div class="nametag" style="width:{width}cm;height:{height}cm">
    <div class="corner-tr">
        <img src="{corner_tr_url}" style="width:100%;height:100%;display:block" alt="">
    </div>
    <div class="corner-bl">
        <img src="{corner_bl_url}" style="width:100%;height:100%;display:block" alt="">
    </div>

    <div class="head">
        <img src="{logo_url}" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="titles">
            <div class="title-main">STUDY CENTER</div>
            <div class="title-sub">STUDY CENTER KABUPATEN NIAS</div>
            <div class="title-tag">SECOND HOME FOR THE BETTER FUTURE</div>
        </div>
    </div>
    <div class="divider"></div>

    <div class="body">
        <div class="row-line">
            <span class="label">Nama</span><span class="colon">:</span>
            <span class="value">{name}</span>
        </div>
        <div class="row-line">
            <span class="label">Kelas</span><span class="colon">:</span>
            <span class="value">{kelas}</span>
        </div>
        <div class="row-line">
            <span class="label">Sekolah</span><span class="colon">:</span>
            <span class="value">{sekolah}</span>
        </div>
    </div>
    <div style="position:absolute;bottom:3mm;right:3mm;z-index:2;width:14mm;height:14mm;overflow:hidden">
        {qr_html}
    </div>
</div>
HTML,
            ],
            [
                'slug'        => 'with_photo',
                'name'        => 'Dengan Foto',
                'description' => 'Foto profil siswa (3:4) di kiri, info di kanan.',
                'width'       => 9.0,
                'height'      => 6.0,
                'orientation' => 'portrait',
                'is_system'   => true,
                'html_content' => <<<'HTML'
<div class="nametag nametag-photo" style="width:{width}cm;height:{height}cm">
    <div class="corner-tr">
        <img src="{corner_tr_url}" style="width:100%;height:100%;display:block" alt="">
    </div>
    <div class="corner-bl">
        <img src="{corner_bl_url}" style="width:100%;height:100%;display:block" alt="">
    </div>

    <div class="head">
        <img src="{logo_url}" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="titles">
            <div class="title-main">STUDY CENTER</div>
            <div class="title-sub">STUDY CENTER KABUPATEN NIAS</div>
            <div class="title-tag">SECOND HOME FOR THE BETTER FUTURE</div>
        </div>
    </div>
    <div class="divider"></div>

    <div class="photo-body">
        <div class="photo-box">
            {photo_html}
        </div>
        <div class="info-col">
            <div class="info-name">{name}</div>
            <div class="info-row">
                <span class="info-label">Kelas</span>
                <span class="info-sep">:</span>
                <span class="info-val">{kelas}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sekolah</span>
                <span class="info-sep">:</span>
                <span class="info-val">{sekolah}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cabang</span>
                <span class="info-sep">:</span>
                <span class="info-val">{cabang}</span>
            </div>
            <div style="margin-top:2mm;width:12mm;height:12mm;overflow:hidden">
                {qr_html}
            </div>
        </div>
    </div>
</div>
HTML,
            ],
            [
                'slug'        => 'landscape',
                'name'        => 'Landscape',
                'description' => 'Orientasi horizontal dengan foto dan info.',
                'width'       => 9.0,
                'height'      => 5.5,
                'orientation' => 'landscape',
                'is_system'   => true,
                'html_content' => <<<'HTML'
<div class="nametag nametag-landscape" style="width:{width}cm;height:{height}cm">
    <div class="corner-tr">
        <img src="{corner_tr_url}" style="width:100%;height:100%;display:block" alt="">
    </div>
    <div class="corner-bl">
        <img src="{corner_bl_url}" style="width:100%;height:100%;display:block" alt="">
    </div>

    <div class="ls-photo">
        {photo_html}
    </div>

    <div class="ls-content">
        <div class="ls-header">
            <img src="{logo_url}" alt="Logo" class="logo-sm" onerror="this.style.display='none'">
            <div class="ls-brand">
                <div class="ls-title">STUDY CENTER</div>
                <div class="ls-sub">KABUPATEN NIAS</div>
            </div>
        </div>
        <div class="ls-divider"></div>
        <div class="ls-name">{name}</div>
        <div class="ls-meta">
            <span class="ls-badge">{kelas}</span>
            <span class="ls-badge">{sekolah}</span>
            <span class="ls-badge ls-badge-cabang">{cabang}</span>
        </div>
    </div>
    <div style="flex-shrink:0;width:14mm;height:14mm;overflow:hidden;align-self:center;margin-left:2mm">
        {qr_html}
    </div>
</div>
HTML,
            ],
            [
                'slug'        => 'portrait_large',
                'name'        => 'Portrait Besar (Foto + QR)',
                'description' => 'Potrait 8.5×11cm dengan foto besar dan QR code.',
                'width'       => 8.5,
                'height'      => 11.0,
                'orientation' => 'portrait',
                'is_system'   => true,
                'html_content' => <<<'HTML'
<div class="nametag nametag-lg" style="width:{width}cm;height:{height}cm">
    <div class="corner-tr">
        <img src="{corner_tr_url}" style="width:100%;height:100%;display:block" alt="">
    </div>
    <div class="corner-bl">
        <img src="{corner_bl_url}" style="width:100%;height:100%;display:block" alt="">
    </div>

    <div class="head">
        <img src="{logo_url}" alt="Logo" class="logo" onerror="this.style.display='none'">
        <div class="titles">
            <div class="title-main">STUDY CENTER</div>
            <div class="title-sub">STUDY CENTER KABUPATEN NIAS</div>
            <div class="title-tag">SECOND HOME FOR THE BETTER FUTURE</div>
        </div>
    </div>
    <div class="divider"></div>

    <div class="lg-photo-wrap">
        <div class="lg-photo-box">
            {photo_html}
        </div>
    </div>

    <div class="lg-info">
        <div class="lg-name">{name}</div>
        <div class="lg-rows">
            <div class="lg-row">
                <span class="lg-label">Kelas</span>
                <span class="lg-colon">:</span>
                <span class="lg-val">{kelas}</span>
            </div>
            <div class="lg-row">
                <span class="lg-label">Sekolah</span>
                <span class="lg-colon">:</span>
                <span class="lg-val">{sekolah}</span>
            </div>
            <div class="lg-row">
                <span class="lg-label">Cabang</span>
                <span class="lg-colon">:</span>
                <span class="lg-val">{cabang}</span>
            </div>
        </div>
    </div>

    <div class="lg-footer">
        <div class="lg-qr">{qr_html}</div>
    </div>
</div>
HTML,
            ],
        ];

        foreach ($templates as $tpl) {
            NameTagTemplate::updateOrCreate(['slug' => $tpl['slug']], $tpl);
        }
    }
}
