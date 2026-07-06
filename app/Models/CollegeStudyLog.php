<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeStudyLog extends Model
{
    protected $table = 'college_study_logs';

    protected $fillable = [
        'user_id', 'life_item_id', 'tanggal',
        'jam_mulai', 'jam_selesai', 'tipe',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lifeItem()
    {
        return $this->belongsTo(JurnalLifeItem::class, 'life_item_id');
    }

    public function totalMinutes(): int
    {
        $start = \Carbon\Carbon::createFromTimeString($this->jam_mulai);
        $end   = \Carbon\Carbon::createFromTimeString($this->jam_selesai);
        return max(0, (int) $start->diffInMinutes($end, false));
    }
}
