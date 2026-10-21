<?php

namespace Tests\Feature;

use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceSession;
use App\Models\CourseClass;
use App\Models\Lecturer;
use App\Models\Meeting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::AdminAcademic]);
    }

    private function lecturer(): Lecturer
    {
        return Lecturer::factory()->create();
    }

    private function student(): Student
    {
        return Student::factory()->create();
    }

    /**
     * Kelas dengan dosen pengampu dan mahasiswa enrolled.
     */
    private function classWithPeople(?Lecturer $lecturer = null, ?Student $student = null): CourseClass
    {
        $class = CourseClass::factory()->create();

        if ($lecturer) {
            $class->classLecturers()->create(['lecturer_id' => $lecturer->id, 'role' => 'pengampu']);
        }

        if ($student) {
            $class->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);
        }

        return $class;
    }

    private function meetingFor(CourseClass $class, int $number = 1): Meeting
    {
        return Meeting::factory()->create([
            'course_class_id' => $class->id,
            'meeting_number' => $number,
        ]);
    }

    public function test_mahasiswa_luar_kelas_ditolak_akses_pertemuan(): void
    {
        $class = $this->classWithPeople();
        $other = $this->classWithPeople();
        $meeting = $this->meetingFor($class);
        $stranger = $this->student();

        $this->actingAs($stranger->user)->get(route('meetings.index', $class))->assertForbidden();
        $this->actingAs($stranger->user)->get(route('meetings.show', [$class, $meeting]))->assertForbidden();
        $this->actingAs($stranger->user)->get(route('meetings.session.show', [$class, $meeting]))->assertForbidden();
    }

    public function test_dosen_kelas_lain_ditolak_akses_pertemuan(): void
    {
        $class = $this->classWithPeople();
        $lecturer = $this->lecturer();
        $meeting = $this->meetingFor($class);

        $this->actingAs($lecturer->user)->get(route('meetings.index', $class))->assertForbidden();
        $this->actingAs($lecturer->user)->get(route('meetings.show', [$class, $meeting]))->assertForbidden();
    }

    public function test_pertemuan_lintas_kelas_ditolak(): void
    {
        $class = $this->classWithPeople();
        $other = $this->classWithPeople();
        $meeting = $this->meetingFor($class);

        // Meeting milik kelas A tidak boleh diakses lewat route kelas B.
        $this->actingAs($this->admin())
            ->get(route('meetings.show', [$other, $meeting]))
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->get(route('meetings.session.show', [$other, $meeting]))
            ->assertNotFound();
    }

    public function test_mahasiswa_tidak_boleh_membuka_sesi_presensi(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople(null, $student);
        $meeting = $this->meetingFor($class);

        $this->actingAs($student->user)
            ->post(route('meetings.session.open', [$class, $meeting]))
            ->assertForbidden();

        $this->assertDatabaseMissing('attendance_sessions', ['meeting_id' => $meeting->id]);
    }

    public function test_dosen_bukan_pengampu_tidak_boleh_mencatat_kehadiran(): void
    {
        $class = $this->classWithPeople();
        $otherLecturer = $this->lecturer();
        $student = $this->student();
        $class->enrollments()->create(['student_id' => $student->id, 'status' => 'aktif']);
        $meeting = $this->meetingFor($class);
        $session = AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($otherLecturer->user)
            ->post(route('meetings.session.attendances.store', [$class, $meeting]), [
                'student_id' => $student->id,
                'status' => AttendanceStatus::Present->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_dosen_membuka_sesi_presensi(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);
        $meeting = $this->meetingFor($class);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.session.open', [$class, $meeting]))
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_sessions', [
            'meeting_id' => $meeting->id,
            'status' => AttendanceSessionStatus::Open->value,
        ]);
    }

    public function test_sesi_tidak_otomatis_terbuka_saat_halaman_dibuka(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);
        $meeting = $this->meetingFor($class);

        $this->actingAs($lecturer->user)
            ->get(route('meetings.session.show', [$class, $meeting]))
            ->assertOk();

        $this->assertDatabaseHas('attendance_sessions', [
            'meeting_id' => $meeting->id,
            'status' => AttendanceSessionStatus::NotStarted->value,
        ]);
    }

    public function test_menutup_sesi_yang_belum_dibuka_ditolak(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.session.close', [$class, $meeting]))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('attendance_sessions', [
            'meeting_id' => $meeting->id,
            'status' => AttendanceSessionStatus::NotStarted->value,
        ]);
    }

    public function test_mahasiswa_tidak_bisa_presensi_diri_sebelum_sesi_dibuka(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople(null, $student);
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($student->user)
            ->post(route('meetings.session.self', [$class, $meeting]))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_mahasiswa_presensi_diri_saat_sesi_terbuka(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople(null, $student);
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($student->user)
            ->post(route('meetings.session.self', [$class, $meeting]))
            ->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'status' => AttendanceStatus::Present->value,
            'method' => 'self',
        ]);
    }

    public function test_presensi_diri_idempotent(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople(null, $student);
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($student->user)->post(route('meetings.session.self', [$class, $meeting]));
        $this->actingAs($student->user)->post(route('meetings.session.self', [$class, $meeting]));

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_mahasiswa_luar_kelas_tidak_bisa_presensi_diri(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople();
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($student->user)
            ->post(route('meetings.session.self', [$class, $meeting]))
            ->assertForbidden();

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_dosen_mencatat_kehadiran_mahasiswa(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.session.attendances.store', [$class, $meeting]), [
                'student_id' => $student->id,
                'status' => AttendanceStatus::Late->value,
                'note' => 'Terlambat 10 menit',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'status' => AttendanceStatus::Late->value,
            'method' => 'dosen',
            'note' => 'Terlambat 10 menit',
        ]);
    }

    public function test_pencatatan_kehadiran_ditolak_bila_sesi_belum_dibuka(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.session.attendances.store', [$class, $meeting]), [
                'student_id' => $student->id,
                'status' => AttendanceStatus::Present->value,
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_pencatatan_mahasiswa_tidak_enrolled_ditolak(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);
        $stranger = $this->student();
        $meeting = $this->meetingFor($class);
        AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.session.attendances.store', [$class, $meeting]), [
                'student_id' => $stranger->id,
                'status' => AttendanceStatus::Present->value,
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_koreksi_presensi_mencatat_audit(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);
        $meeting = $this->meetingFor($class);
        $session = AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);
        $attendance = Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent->value,
        ]);

        $this->actingAs($lecturer->user)
            ->put(route('meetings.session.attendances.correct', [$class, $meeting, $attendance]), [
                'status' => AttendanceStatus::Present->value,
                'reason' => 'Sudah hadir,_presentasi_ disGMT',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => AttendanceStatus::Present->value,
        ]);

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'old_status' => AttendanceStatus::Absent->value,
            'new_status' => AttendanceStatus::Present->value,
            'actor_id' => $lecturer->user->id,
        ]);
    }

    public function test_koreksi_presensi_wajib_alasan(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);
        $meeting = $this->meetingFor($class);
        $session = AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);
        $attendance = Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent->value,
        ]);

        $this->actingAs($lecturer->user)
            ->put(route('meetings.session.attendances.correct', [$class, $meeting, $attendance]), [
                'status' => AttendanceStatus::Present->value,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('attendance_corrections', 0);
    }

    public function test_mahasiswa_tidak_boleh_melakukan_koreksi(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople(null, $student);
        $meeting = $this->meetingFor($class);
        $session = AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);
        $attendance = Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent->value,
        ]);

        $this->actingAs($student->user)
            ->put(route('meetings.session.attendances.correct', [$class, $meeting, $attendance]), [
                'status' => AttendanceStatus::Present->value,
                'reason' => 'Mau ubah sendiri',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('attendance_corrections', 0);
    }

    public function test_pertemuan_dengan_presensi_tidak_bisa_dihapus(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);
        $meeting = $this->meetingFor($class);
        $session = AttendanceSession::factory()->open()->create(['meeting_id' => $meeting->id]);
        Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
        ]);

        $this->actingAs($lecturer->user)
            ->delete(route('meetings.destroy', [$class, $meeting]))
            ->assertSessionHasErrors('meeting');

        $this->assertDatabaseHas('meetings', ['id' => $meeting->id]);
    }

    public function test_pertemuan_tanpa_presensi_bisa_dihapus(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);
        $meeting = $this->meetingFor($class);

        $this->actingAs($lecturer->user)
            ->delete(route('meetings.destroy', [$class, $meeting]))
            ->assertRedirect(route('meetings.index', $class));

        $this->assertDatabaseMissing('meetings', ['id' => $meeting->id]);
    }

    public function test_dosen_pengampu_bisa_membuat_pertemuan(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.store', $class), [
                'meeting_number' => 1,
                'title' => 'Pertemuan 1',
            ])
            ->assertRedirect(route('meetings.index', $class));

        $this->assertDatabaseHas('meetings', [
            'course_class_id' => $class->id,
            'meeting_number' => 1,
        ]);
    }

    public function test_mahasiswa_tidak_bisa_membuat_pertemuan(): void
    {
        $student = $this->student();
        $class = $this->classWithPeople(null, $student);

        $this->actingAs($student->user)
            ->post(route('meetings.store', $class), [
                'meeting_number' => 1,
                'title' => 'Ilegal',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('meetings', 0);
    }

    public function test_nomor_pertemuan_unik_per_kelas(): void
    {
        $lecturer = $this->lecturer();
        $class = $this->classWithPeople($lecturer);
        $this->meetingFor($class, 1);

        $this->actingAs($lecturer->user)
            ->post(route('meetings.store', $class), [
                'meeting_number' => 1,
                'title' => 'Duplikat',
            ])
            ->assertSessionHasErrors('meeting_number');
    }

    public function test_rekap_kelas_hanya_untuk_admin_atau_dosen_pengampu(): void
    {
        $class = $this->classWithPeople();
        $otherLecturer = $this->lecturer();
        $student = $this->student();

        $this->actingAs($otherLecturer->user)
            ->get(route('attendance.recap.class', $class))
            ->assertForbidden();

        $this->actingAs($student->user)
            ->get(route('attendance.recap.class', $class))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('attendance.recap.class', $class))
            ->assertOk();
    }

    public function test_rekap_mahasiswa_hanya_melihat_miliknya(): void
    {
        $student = $this->student();
        $other = $this->student();
        $class = $this->classWithPeople(null, $student);
        $class->enrollments()->create(['student_id' => $other->id, 'status' => 'aktif']);

        $this->actingAs($student->user)
            ->get(route('attendance.recap.student', [$class, $other]))
            ->assertForbidden();

        $this->actingAs($student->user)
            ->get(route('attendance.recap.student', [$class, $student]))
            ->assertOk();
    }

    public function test_rekap_mahasiswa_menghitung_hadir_dan_terlambat_sebagai_hadir(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);

        $meeting1 = $this->meetingFor($class, 1);
        $meeting2 = $this->meetingFor($class, 2);
        $meeting3 = $this->meetingFor($class, 3);

        $session1 = AttendanceSession::factory()->closed()->create(['meeting_id' => $meeting1->id]);
        $session2 = AttendanceSession::factory()->closed()->create(['meeting_id' => $meeting2->id]);
        $session3 = AttendanceSession::factory()->closed()->create(['meeting_id' => $meeting3->id]);

        Attendance::factory()->create([
            'attendance_session_id' => $session1->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Present->value,
        ]);
        Attendance::factory()->create([
            'attendance_session_id' => $session2->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Late->value,
        ]);
        Attendance::factory()->create([
            'attendance_session_id' => $session3->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Absent->value,
        ]);

        $response = $this->actingAs($student->user)
            ->get(route('attendance.recap.student', [$class, $student]));

        $response->assertOk();
        $response->assertSee('66.7%');
    }

    public function test_rekap_kelas_menampilkan_total_status(): void
    {
        $lecturer = $this->lecturer();
        $student = $this->student();
        $class = $this->classWithPeople($lecturer, $student);
        $meeting = $this->meetingFor($class, 1);
        $session = AttendanceSession::factory()->closed()->create(['meeting_id' => $meeting->id]);

        Attendance::factory()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Present->value,
        ]);

        $this->actingAs($lecturer->user)
            ->get(route('attendance.recap.class', $class))
            ->assertOk()
            ->assertSee('Hadir');
    }
}
