<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NameTagTemplate extends Model
{
    protected $table = 'nametag_templates';

    protected $fillable = [
        'slug', 'name', 'description',
        'width', 'height', 'orientation',
        'html_content', 'is_system',
    ];

    protected $casts = [
        'width'     => 'float',
        'height'    => 'float',
        'is_system' => 'boolean',
    ];

    // Replace {placeholders} with student data
    public function render(array $data): string
    {
        $placeholders = [
            '{name}'          => $data['name']          ?? '',
            '{kelas}'         => $data['kelas']         ?? '',
            '{sekolah}'       => $data['sekolah']       ?? '',
            '{cabang}'        => $data['cabang']        ?? '',
            '{photo_url}'     => $data['photo_url']     ?? '',
            '{photo_html}'    => $data['photo_html']    ?? '',
            '{qr_html}'       => $data['qr_html']       ?? '',
            '{logo_url}'      => $data['logo_url']      ?? '',
            '{corner_tr_url}' => $data['corner_tr_url'] ?? '',
            '{corner_bl_url}' => $data['corner_bl_url'] ?? '',
            '{width}'         => $data['width']         ?? $this->width,
            '{height}'        => $data['height']        ?? $this->height,
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $this->html_content);
    }
}
