<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseClass;
use App\Models\Lecturer;
use App\Models\Meeting;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterAttendanceRecapTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::AdminAcademic]);
    }

    private function student(): Student
    {
        return Student::factory()->create();
    }

    /**
     * Buat kelas pada semester tertentu dengan dosen pengampu & mahasiswa enrolled.
     */
    private function classOn(
        Semester $semester,
        ?Lecturer $lecturer = null,
        ?Student $student = null
    ): CourseClass {
        $class = CourseClass::factory()->create(['semester_id' => $semester->id]);

        if ($lecturer) {
            $class->classLecturers()->create(['lecturer_id' => $lecturer->id, 'role' => 'pengampu']);
        }

        if ($student) {
            $class->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);
        }

        return $class;
    }

    /**
     * Buat (atau ambil) pertemuan + sesi presensi untuk nomor pertemuan tertentu.
     */
    private function sessionFor(CourseClass $class, int $meetingNumber): AttendanceSession
    {
        $meeting = Meeting::firstOrCreate(
            [
                'course_class_id' => $class->id,
                'meeting_number' => $meetingNumber,
            ],
            [
                'title' => 'Pertemuan '.$meetingNumber,
            ]
        );

        return AttendanceSession::firstOrCreate(
            ['meeting_id' => $meeting->id],
            ['status' => 'belum']
        );
    }

    /**
     * Catat kehadiran satu mahasiswa pada pertemuan tertentu.
     */
    private function record(
        CourseClass $class,
        Student $student,
        AttendanceStatus $status,
        int $meetingNumber = 1
    ): void {
        $session = $this->sessionFor($class, $meetingNumber);

        Attendance::firstOrCreate(
            [
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
            ],
            [
                'status' => $status->value,
            ]
        );
    }

    public function test_mahasiswa_tidak_boleh_membuka_rekap_semester(): void
    {
        $semester = Semester::factory()->create();
        $this->classOn($semester);

        $this->actingAs($this->student()->user)
            ->get(route('attendance.recap.semesters'))
            ->assertForbidden();

        $this->actingAs($this->student()->user)
            ->get(route('attendance.recap.semester', $semester))
            ->assertForbidden();
    }

    public function test_admin_dan_dosen_boleh_membuka_rekap_semester(): void
    {
        $semester = Semester::factory()->create();
        $this->classOn($semester);

        $this->actingAs($this->admin())
            ->get(route('attendance.recap.semesters'))
            ->assertOk();

        $this->actingAs($this->admin())
            ->get(route('attendance.recap.semester', $semester))
            ->assertOk();
    }

    public function test_dosen_hanya_melihat_kelas_yang_diampu(): void
    {
        $semester = Semester::factory()->create();
        $lecturer = Lecturer::factory()->create();
        $otherLecturer = Lecturer::factory()->create();

        $myClass = $this->classOn($semester, $lecturer);
        $otherClass = $this->classOn($semester, $otherLecturer);

        $student = Student::factory()->create();
        $myClass->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);

        $response = $this->actingAs($lecturer->user)
            ->get(route('attendance.recap.semester', $semester));

        $response->assertOk();
        $response->assertSee($myClass->displayName());
        $response->assertDontSee($otherClass->displayName());
    }

    public function test_rekap_menjumlahkan_status_per_kelas(): void
    {
        $semester = Semester::factory()->create();
        $student = Student::factory()->create();
        $class = $this->classOn($semester, null, $student);

        $this->record($class, $student, AttendanceStatus::Present, 1);
        $this->record($class, $student, AttendanceStatus::Late, 2);
        $this->record($class, $student, AttendanceStatus::Absent, 3);

        $response = $this->actingAs($this->admin())
            ->get(route('attendance.recap.semester', $semester));

        $response->assertOk();
        // Hadir + terlambat dihitung sebagai hadir: 2 dari 3 pertemuan = 66.7%.
        $response->assertSee('66.7%');
        $response->assertSee($class->displayName());
    }

    public function test_persentase_kelas_tidak_melebihi_100_persen_dengan_banyak_mahasiswa(): void
    {
        $semester = Semester::factory()->create();
        $students = Student::factory()->count(3)->create();
        $class = $this->classOn($semester);

        foreach ($students as $student) {
            $class->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);
        }

        // Semua mahasiswa hadir di 2 pertemuan = 6 catatan hadir.
        $this->record($class, $students[0], AttendanceStatus::Present, 1);
        $this->record($class, $students[1], AttendanceStatus::Present, 1);
        $this->record($class, $students[2], AttendanceStatus::Present, 1);
        $this->record($class, $students[0], AttendanceStatus::Present, 2);
        $this->record($class, $students[1], AttendanceStatus::Present, 2);
        $this->record($class, $students[2], AttendanceStatus::Present, 2);

        $response = $this->actingAs($this->admin())
            ->get(route('attendance.recap.semester', $semester));

        $response->assertOk();
        // 6 hadir dari 6 catatan = 100%, bukan 300%.
        $response->assertSee('100%');
        $response->assertDontSee('300%');
    }

    public function test_mahasiswa_tanpa_catatan_presensi_terhitung_belum(): void
    {
        $semester = Semester::factory()->create();
        $student = Student::factory()->create();
        $class = $this->classOn($semester, null, $student);

        $this->record($class, $student, AttendanceStatus::Present, 1);

        // Pertemuan kedua tanpa catatan presensi sama sekali.
        Meeting::factory()->create([
            'course_class_id' => $class->id,
            'meeting_number' => 2,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('attendance.recap.semester', $semester));

        $response->assertOk();
        // 1 hadir dari 2 pertemuan = 50%.
        $response->assertSee('50%');
    }

    public function test_persentase_kelas_menggunakan_potensi_mahasiswa_x_pertemuan(): void
    {
        $semester = Semester::factory()->create();
        $students = Student::factory()->count(2)->create();
        $class = $this->classOn($semester);

        foreach ($students as $student) {
            $class->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);
        }

        // Hanya mahasiswa pertama yang tercatat hadir di 1 dari 2 pertemuan.
        $this->record($class, $students[0], AttendanceStatus::Present, 1);

        Meeting::factory()->create([
            'course_class_id' => $class->id,
            'meeting_number' => 2,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('attendance.recap.semester', $semester));

        $response->assertOk();
        // Potensi = 2 mahasiswa x 2 pertemuan = 4, hadir = 1 -> 25%.
        $response->assertSee('25%');
        // Tidak boleh dihitung dari catatan yang tercatat saja (1 dari 1 = 100%).
        $response->assertDontSee('100%');
    }

    public function test_enrollment_tidak_aktif_tidak_dihitung_dalam_rekap(): void
    {
        $semester = Semester::factory()->create();
        $active = Student::factory()->create();
        $inactive = Student::factory()->create();
        $class = $this->classOn($semester);

        $class->enrollments()->create(['student_id' => $active->id, 'status' => 'aktif']);
        $class->enrollments()->create(['student_id' => $inactive->id, 'status' => 'nonaktif']);

        $this->record($class, $active, AttendanceStatus::Present, 1);

        $response = $this->actingAs($this->admin())
            ->get(route('attendance.recap.semester', $semester));

        $response->assertOk();
        $response->assertSee($active->nim);
        $response->assertDontSee($inactive->nim);
        // 1 hadir dari potensi 1 mahasiswa x 1 pertemuan = 100%.
        $response->assertSee('100%');
    }
}
