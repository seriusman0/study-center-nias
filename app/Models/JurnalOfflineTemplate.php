<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalOfflineTemplate extends Model
{
    protected $table = 'jurnal_offline_templates';

    protected $fillable = ['cabang_id', 'original_name', 'path', 'uploaded_by'];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
