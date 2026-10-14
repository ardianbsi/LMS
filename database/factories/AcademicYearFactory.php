<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->numberBetween(2023, 2027);

        return [
            'code' => $start.'/'.($start + 1),
            'name' => 'Tahun Akademik '.$start.'/'.($start + 1),
            'start_date' => ($start).'-08-01',
            'end_date' => ($start + 1).'-07-31',
            'is_active' => true,
        ];
    }
}
