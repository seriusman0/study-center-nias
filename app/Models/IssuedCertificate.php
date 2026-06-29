<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_sertifikat',
        'user_id',
        'template_id',
        'issued_by',
        'tanggal_lulus',
        'nama_kursus',
        'file_path',
        'issued_at',
    ];

    protected $casts = [
        'tanggal_lulus' => 'date',
        'issued_at'     => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
