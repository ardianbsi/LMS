<?php

namespace Database\Factories;

use App\Models\CourseClass;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_class_id' => CourseClass::factory(),
            'meeting_number' => fake()->unique()->numberBetween(1, 1000),
            'title' => fake()->sentence(4),
            'meeting_date' => fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
            'topic' => fake()->sentence(8),
            'summary' => fake()->paragraph(),
        ];
    }
}
