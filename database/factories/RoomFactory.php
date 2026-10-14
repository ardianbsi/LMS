<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('R-###')),
            'name' => 'Ruang '.fake()->unique()->bothify('###'),
            'building' => 'Gedung '.fake()->randomLetter(),
            'capacity' => fake()->numberBetween(20, 120),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
