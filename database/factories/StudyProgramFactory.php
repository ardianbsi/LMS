<?php

namespace Database\Factories;

use App\Models\Faculty;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyProgram>
 */
class StudyProgramFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faculty_id' => Faculty::factory(),
            'code' => strtoupper(fake()->unique()->bothify('PS-###')),
            'name' => 'Program Studi '.fake()->unique()->words(2, true),
            'degree' => fake()->randomElement(['D3', 'S1', 'S2', 'S3']),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
