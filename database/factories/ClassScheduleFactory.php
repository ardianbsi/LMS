<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\CourseClass;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSchedule>
 */
class ClassScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_class_id' => CourseClass::factory(),
            'room_id' => Room::factory(),
            'day_of_week' => fake()->randomElement(['senin', 'selasa', 'rabu', 'kamis', 'jumat']),
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
            'valid_from' => null,
            'valid_until' => null,
            'note' => null,
        ];
    }
}
