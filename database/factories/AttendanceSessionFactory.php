<?php

namespace Database\Factories;

use App\Enums\AttendanceSessionStatus;
use App\Models\AttendanceSession;
use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'status' => AttendanceSessionStatus::NotStarted->value,
            'opened_at' => null,
            'closed_at' => null,
            'opened_by' => null,
            'closed_by' => null,
        ];
    }

    /**
     * Sesi yang sudah dibuka (siap menerima presensi).
     */
    public function open(): static
    {
        return $this->state(fn () => [
            'status' => AttendanceSessionStatus::Open->value,
            'opened_at' => now(),
        ]);
    }

    /**
     * Sesi yang sudah ditutup.
     */
    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => AttendanceSessionStatus::Closed->value,
            'opened_at' => now(),
            'closed_at' => now(),
        ]);
    }
}
