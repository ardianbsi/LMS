<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\CourseClass;
use App\Models\Lecturer;
use App\Models\Room;
use App\Services\ScheduleConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_bentrok_ruang_terdeteksi_dengan_penjelasan(): void
    {
        $room = Room::factory()->create(['code' => 'R-101']);
        $classA = CourseClass::factory()->create();
        $classB = CourseClass::factory()->create();

        ClassSchedule::factory()->create([
            'course_class_id' => $classA->id,
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
        ]);

        $scheduleB = ClassSchedule::factory()->make([
            'course_class_id' => $classB->id,
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '09:00:00',
            'end_time' => '10:40:00',
        ]);
        $scheduleB->courseClass()->associate($classB);

        $conflicts = app(ScheduleConflictService::class)->checkForSchedule($scheduleB);

        $this->assertNotEmpty($conflicts);
        $this->assertEquals('ruang', $conflicts[0]['type']);
        $this->assertStringContainsString('R-101', $conflicts[0]['message']);
        $this->assertStringContainsString('Senin', $conflicts[0]['message']);
    }

    public function test_jadwal_bersebelahan_tidak_bentrok(): void
    {
        $room = Room::factory()->create();

        ClassSchedule::factory()->create([
            'course_class_id' => CourseClass::factory()->create()->id,
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
        ]);

        $classB = CourseClass::factory()->create();
        $scheduleB = ClassSchedule::factory()->make([
            'course_class_id' => $classB->id,
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '09:40:00',
            'end_time' => '11:20:00',
        ]);
        $scheduleB->courseClass()->associate($classB);

        $this->assertSame([], app(ScheduleConflictService::class)->checkForSchedule($scheduleB));
    }

    public function test_hari_beda_tidak_bentrok(): void
    {
        $room = Room::factory()->create();

        ClassSchedule::factory()->create([
            'course_class_id' => CourseClass::factory()->create()->id,
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
        ]);

        $classB = CourseClass::factory()->create();
        $scheduleB = ClassSchedule::factory()->make([
            'course_class_id' => $classB->id,
            'room_id' => $room->id,
            'day_of_week' => 'selasa',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
        ]);
        $scheduleB->courseClass()->associate($classB);

        $this->assertSame([], app(ScheduleConflictService::class)->checkForSchedule($scheduleB));
    }

    public function test_bentrok_dosen_terdeteksi(): void
    {
        $lecturer = Lecturer::factory()->create();
        $classA = CourseClass::factory()->create();
        $classB = CourseClass::factory()->create();

        $classA->classLecturers()->create(['lecturer_id' => $lecturer->id, 'role' => 'pengampu']);
        $classB->classLecturers()->create(['lecturer_id' => $lecturer->id, 'role' => 'pengampu']);

        ClassSchedule::factory()->create([
            'course_class_id' => $classA->id,
            'room_id' => Room::factory()->create()->id,
            'day_of_week' => 'rabu',
            'start_time' => '10:00:00',
            'end_time' => '11:40:00',
        ]);

        $scheduleB = ClassSchedule::factory()->make([
            'course_class_id' => $classB->id,
            'room_id' => Room::factory()->create()->id,
            'day_of_week' => 'rabu',
            'start_time' => '11:00:00',
            'end_time' => '12:40:00',
        ]);
        $scheduleB->courseClass()->associate($classB->fresh());

        $conflicts = app(ScheduleConflictService::class)->checkForSchedule($scheduleB);

        $this->assertContains('dosen', array_column($conflicts, 'type'));
    }

    public function test_rentang_tanggal_tidak_bertumpuk_tidak_bentrok(): void
    {
        $room = Room::factory()->create();

        ClassSchedule::factory()->create([
            'course_class_id' => CourseClass::factory()->create()->id,
            'room_id' => $room->id,
            'day_of_week' => 'kamis',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
            'valid_from' => '2025-08-01',
            'valid_until' => '2025-10-31',
        ]);

        $classB = CourseClass::factory()->create();
        $scheduleB = ClassSchedule::factory()->make([
            'course_class_id' => $classB->id,
            'room_id' => $room->id,
            'day_of_week' => 'kamis',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
            'valid_from' => '2025-11-01',
            'valid_until' => '2026-01-31',
        ]);
        $scheduleB->courseClass()->associate($classB);

        $this->assertSame([], app(ScheduleConflictService::class)->checkForSchedule($scheduleB));
    }

    public function test_bentrok_mahasiswa_terdeteksi_saat_enrollment(): void
    {
        $student = \App\Models\Student::factory()->create();
        $classA = CourseClass::factory()->create();
        $classB = CourseClass::factory()->create();

        $classA->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);

        ClassSchedule::factory()->create([
            'course_class_id' => $classA->id,
            'day_of_week' => 'jumat',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
        ]);

        $classB->schedules()->create([
            'room_id' => Room::factory()->create()->id,
            'day_of_week' => 'jumat',
            'start_time' => '09:00:00',
            'end_time' => '10:40:00',
        ]);

        $conflicts = app(ScheduleConflictService::class)->checkStudentEnrollment($student->id, $classB->fresh());

        $this->assertNotEmpty($conflicts);
        $this->assertEquals('mahasiswa', $conflicts[0]['type']);
    }
}
