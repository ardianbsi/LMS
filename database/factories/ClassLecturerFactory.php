<?php

namespace Database\Factories;

use App\Models\ClassLecturer;
use App\Models\CourseClass;
use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassLecturer>
 */
class ClassLecturerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_class_id' => CourseClass::factory(),
            'lecturer_id' => Lecturer::factory(),
            'role' => 'pengampu',
        ];
    }
}
