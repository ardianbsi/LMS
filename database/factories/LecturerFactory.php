<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lecturer>
 */
class LecturerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => UserRole::Lecturer]),
            'study_program_id' => StudyProgram::factory(),
            'nip' => fake()->unique()->bothify('##################'),
            'academic_title' => fake()->optional()->randomElement(['S.T.', 'M.T.', 'Dr.', 'Prof.']),
            'phone' => fake()->optional()->phoneNumber(),
            'status' => 'aktif',
        ];
    }
}
