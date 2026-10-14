<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ganjil = fake()->boolean();

        return [
            'academic_year_id' => AcademicYear::factory(),
            'term_code' => fake()->unique()->bothify($ganjil ? '######-1' : '######-2'),
            'name' => $ganjil ? 'Ganjil' : 'Genap',
            'start_date' => $ganjil ? '2025-08-01' : '2026-02-01',
            'end_date' => $ganjil ? '2026-01-31' : '2026-07-31',
            'is_active' => true,
        ];
    }
}
