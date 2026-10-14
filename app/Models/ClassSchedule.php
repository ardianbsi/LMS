<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Database\Factories\ClassScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['course_class_id', 'room_id', 'day_of_week', 'start_time', 'end_time', 'valid_from', 'valid_until', 'note'])]
class ClassSchedule extends Model
{
    /** @use HasFactory<ClassScheduleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => DayOfWeek::class,
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Label tampilan: "Senin 08:00–09:40 @ R-101".
     */
    public function displayLabel(): string
    {
        $day = $this->day_of_week instanceof DayOfWeek ? $this->day_of_week->label() : (string) $this->day_of_week;
        $start = substr((string) $this->start_time, 0, 5);
        $end = substr((string) $this->end_time, 0, 5);
        $room = $this->room?->code ? " @ {$this->room->code}" : '';

        return "{$day} {$start}–{$end}{$room}";
    }
}
