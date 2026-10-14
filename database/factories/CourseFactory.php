<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_program_id' => StudyProgram::factory(),
            'code' => strtoupper(fake()->unique()->bothify('MK###')),
            'name' => 'Mata Kuliah '.fake()->unique()->words(3, true),
            'credits' => fake()->numberBetween(1, 6),
            'semester_number' => fake()->numberBetween(1, 8),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
