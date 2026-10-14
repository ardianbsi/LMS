<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseClass>
 */
class CourseClassFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'semester_id' => Semester::factory(),
            'class_code' => strtoupper(fake()->randomLetter()),
            'name' => null,
            'capacity' => fake()->optional()->numberBetween(20, 60),
            'status' => 'aktif',
        ];
    }
}
