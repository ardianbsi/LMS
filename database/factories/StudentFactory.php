<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Student]),
            'study_program_id' => StudyProgram::factory(),
            'nim' => fake()->unique()->bothify('##########'),
            'entry_year' => fake()->numberBetween(2020, 2026),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'status' => 'aktif',
        ];
    }
}
