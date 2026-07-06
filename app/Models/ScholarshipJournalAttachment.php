<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipJournalAttachment extends Model
{
    protected $fillable = ['journal_id', 'file_name', 'file_path', 'file_type'];

    public function journal()
    {
        return $this->belongsTo(ScholarshipJournal::class, 'journal_id');
    }
}
