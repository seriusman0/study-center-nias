<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipJournalItem extends Model
{
    protected $fillable = [
        'journal_id',
        'gpa_current_semester', 'cumulative_gpa', 'academic_summary', 'class_attendance_percentage',
        'organization_activities', 'training_seminars', 'achievements',
        'community_service_details', 'service_hours',
        'personal_reflection', 'next_month_goals',
    ];

    public function journal()
    {
        return $this->belongsTo(ScholarshipJournal::class, 'journal_id');
    }
}
