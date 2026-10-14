<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::AdminAcademic]);
    }

    public function test_admin_dapat_membuat_kelas(): void
    {
        $course = Course::factory()->create();
        $semester = Semester::factory()->create();

        $response = $this->actingAs($this->admin())->post(route('classes.store'), [
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'class_code' => 'A',
        ]);

        $response->assertRedirect(route('classes.index'));
        $this->assertDatabaseHas('course_classes', [
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'class_code' => 'A',
        ]);
    }

    public function test_enrollment_duplikat_ditolak(): void
    {
        $class = CourseClass::factory()->create();
        $student = Student::factory()->create();

        $this->actingAs($this->admin())->post(route('classes.enrollments.store', $class), [
            'student_id' => $student->id,
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin())->post(route('classes.enrollments.store', $class), [
            'student_id' => $student->id,
        ])->assertSessionHasErrors('student_id');

        $this->assertEquals(1, Enrollment::where('course_class_id', $class->id)->where('student_id', $student->id)->count());
    }

    public function test_penugasan_dosen_duplikat_ditolak(): void
    {
        $class = CourseClass::factory()->create();
        $lecturer = Lecturer::factory()->create();

        $this->actingAs($this->admin())->post(route('classes.lecturers.store', $class), [
            'lecturer_id' => $lecturer->id,
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin())->post(route('classes.lecturers.store', $class), [
            'lecturer_id' => $lecturer->id,
        ])->assertSessionHasErrors('lecturer_id');
    }

    public function test_jadwal_bentrok_ditolak_tidak_disimpan(): void
    {
        $room = \App\Models\Room::factory()->create();
        $classA = CourseClass::factory()->create();
        $classB = CourseClass::factory()->create();

        $this->actingAs($this->admin())->post(route('classes.schedules.store', $classA), [
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '08:00',
            'end_time' => '09:40',
        ])->assertSessionHasNoErrors();

        $response = $this->actingAs($this->admin())->post(route('classes.schedules.store', $classB), [
            'room_id' => $room->id,
            'day_of_week' => 'senin',
            'start_time' => '09:00',
            'end_time' => '10:40',
        ]);

        $response->assertSessionHasErrors('schedule');
        $this->assertEquals(0, $classB->schedules()->count());
    }

    public function test_waktu_selesai_harus_setelah_mulai(): void
    {
        $class = CourseClass::factory()->create();

        $this->actingAs($this->admin())->post(route('classes.schedules.store', $class), [
            'day_of_week' => 'senin',
            'start_time' => '10:00',
            'end_time' => '09:00',
        ])->assertSessionHasErrors('end_time');
    }

    public function test_dosen_hanya_melihat_kelas_yang_ditugaskan(): void
    {
        $lecturer = Lecturer::factory()->create();
        $ownClass = CourseClass::factory()->create();
        $otherClass = CourseClass::factory()->create();
        $ownClass->classLecturers()->create(['lecturer_id' => $lecturer->id]);

        $response = $this->actingAs($lecturer->user)->get(route('classes.index'));

        $response->assertOk();
        $response->assertSee($ownClass->displayName());
        $response->assertDontSee($otherClass->displayName());

        $this->actingAs($lecturer->user)->get(route('classes.show', $otherClass))->assertForbidden();
    }

    public function test_mahasiswa_hanya_melihat_kelas_yang_diikuti(): void
    {
        $student = Student::factory()->create();
        $ownClass = CourseClass::factory()->create();
        $otherClass = CourseClass::factory()->create();
        $ownClass->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);

        $response = $this->actingAs($student->user)->get(route('classes.index'));

        $response->assertOk();
        $response->assertSee($ownClass->displayName());
        $response->assertDontSee($otherClass->displayName());

        $this->actingAs($student->user)->get(route('classes.show', $otherClass))->assertForbidden();
    }

    public function test_filter_semester_dan_hari_bekerja(): void
    {
        $semester = Semester::factory()->create();
        $class = CourseClass::factory()->create(['semester_id' => $semester->id]);
        $class->schedules()->create([
            'room_id' => \App\Models\Room::factory()->create()->id,
            'day_of_week' => 'senin',
            'start_time' => '08:00:00',
            'end_time' => '09:40:00',
        ]);

        $student = Student::factory()->create();
        $class->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);

        $response = $this->actingAs($this->admin())->get(route('classes.index', ['semester_id' => $semester->id]));
        $response->assertOk();
        $response->assertSee($class->displayName());

        $response = $this->actingAs($student->user)->get(route('schedules.mine', ['day' => 'senin']));
        $response->assertOk();

        $response = $this->actingAs($student->user)->get(route('schedules.mine', ['day' => 'selasa']));
        $response->assertOk();
        $response->assertSee('Belum ada jadwal.');
    }
}
