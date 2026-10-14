<?php

namespace App\Models;

use Database\Factories\ClassLecturerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['course_class_id', 'lecturer_id', 'role'])]
class ClassLecturer extends Model
{
    /** @use HasFactory<ClassLecturerFactory> */
    use HasFactory;

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class);
    }
}
