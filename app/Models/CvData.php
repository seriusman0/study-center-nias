<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvData extends Model
{
    protected $fillable = [
        'user_id', 'pendidikan', 'pengalaman', 'keterampilan', 'portofolio', 'template',
    ];

    protected function casts(): array
    {
        return [
            'pendidikan' => 'array',
            'pengalaman' => 'array',
            'keterampilan' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
