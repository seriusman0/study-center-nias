<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalPhotoScan extends Model
{
    protected $table = 'jurnal_photo_scans';

    protected $fillable = [
        'image_path', 'original_name', 'status', 'result_json', 'error_message', 'created_by',
    ];

    protected $casts = [
        'result_json' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isDone(): bool     { return $this->status === 'done'; }
    public function isFailed(): bool   { return $this->status === 'failed'; }
}
