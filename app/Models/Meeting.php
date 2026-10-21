<?php

namespace App\Models;

use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['course_class_id', 'meeting_number', 'title', 'meeting_date', 'start_time', 'end_time', 'topic', 'summary'])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    /**
     * Satu pertemuan memiliki satu sesi presensi.
     */
    public function attendanceSession(): HasOne
    {
        return $this->hasOne(AttendanceSession::class);
    }

    /**
     * Label tampilan: "Pertemuan 3 — Topik".
     */
    public function displayName(): string
    {
        $title = $this->title ?: 'Tanpa judul';

        return "Pertemuan {$this->meeting_number} — {$title}";
    }
}
