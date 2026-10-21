<?php

namespace App\Models;

use Database\Factories\CourseClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'semester_id', 'class_code', 'name', 'capacity', 'status'])]
class CourseClass extends Model
{
    /** @use HasFactory<CourseClassFactory> */
    use HasFactory;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function classLecturers(): HasMany
    {
        return $this->hasMany(ClassLecturer::class);
    }

    /**
     * Pertemuan kelas (Fase 4).
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Dosen pengampu via pivot.
     */
    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(Lecturer::class, 'class_lecturers')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Mahasiswa terdaftar via pivot.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Label tampilan: "Kode MK — Nama MK (Kelas X)".
     */
    public function displayName(): string
    {
        $course = $this->course;

        return $course
            ? "{$course->code} — {$course->name} (Kelas {$this->class_code})"
            : "Kelas {$this->class_code}";
    }
}
