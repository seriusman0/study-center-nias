<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipJournal extends Model
{
    protected $fillable = [
        'user_id', 'title', 'period_month', 'period_year',
        'status', 'submitted_at', 'reviewed_by', 'reviewed_at', 'reviewer_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    const STATUSES = ['draft', 'submitted', 'under_review', 'approved', 'revision_required'];

    const STATUS_LABELS = [
        'draft' => 'Draft',
        'submitted' => 'Menunggu Review',
        'under_review' => 'Sedang Ditinjau',
        'approved' => 'Disetujui',
        'revision_required' => 'Perlu Revisi',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function item()
    {
        return $this->hasOne(ScholarshipJournalItem::class, 'journal_id');
    }

    public function attachments()
    {
        return $this->hasMany(ScholarshipJournalAttachment::class, 'journal_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'revision_required']);
    }
}
