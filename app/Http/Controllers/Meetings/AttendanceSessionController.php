<?php

namespace App\Http\Controllers\Meetings;

use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Meetings\AttendanceCheckRequest;
use App\Http\Requests\Meetings\AttendanceCorrectionRequest;
use App\Http\Requests\Meetings\SelfAttendanceRequest;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseClass;
use App\Models\Meeting;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class AttendanceSessionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Halaman sesi presensi: daftar mahasiswa + status kehadiran.
     */
    public function show(CourseClass $courseClass, Meeting $meeting): View
    {
        $this->authorize('view', $meeting);

        $this->assertBelongsToClass($courseClass, $meeting);

        $session = $this->resolveSession($meeting);

        $session->load(['attendances.student.user', 'attendances.corrections.actor']);

        $enrolled = $courseClass->enrollments()
            ->with('student.user')
            ->where('status', 'aktif')
            ->orderBy('id')
            ->get();

        $attendances = $session->attendances->keyBy('student_id');

        return view('meetings.session', compact('courseClass', 'meeting', 'session', 'enrolled', 'attendances'));
    }

    /**
     * Buka sesi presensi.
     */
    public function open(CourseClass $courseClass, Meeting $meeting, AttendanceService $service): RedirectResponse
    {
        $this->authorize('manageSession', $meeting);

        $this->assertBelongsToClass($courseClass, $meeting);

        $session = $this->resolveSession($meeting);

        $service->openSession($session, request()->user());

        return back()->with('status', 'Sesi presensi dibuka.');
    }

    /**
     * Tutup sesi presensi; setelah ditutup presensi baru ditolak.
     */
    public function close(CourseClass $courseClass, Meeting $meeting, AttendanceService $service): RedirectResponse
    {
        $this->authorize('manageSession', $meeting);

        $this->assertBelongsToClass($courseClass, $meeting);

        $session = $this->resolveSession($meeting);

        try {
            $service->closeSession($session, request()->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Sesi presensi ditutup.');
    }

    /**
     * Dosen/admin menandai status kehadiran mahasiswa.
     */
    public function store(AttendanceCheckRequest $request, CourseClass $courseClass, Meeting $meeting, AttendanceService $service): RedirectResponse
    {
        $this->assertBelongsToClass($courseClass, $meeting);

        $session = $this->resolveSession($meeting);

        $this->authorize('mark', $session);

        $student = Student::findOrFail($request->integer('student_id'));

        if (! $courseClass->enrollments()->where('student_id', $student->id)->where('status', 'aktif')->exists()) {
            return back()->withErrors(['student_id' => 'Mahasiswa tidak terdaftar aktif pada kelas ini.']);
        }

        $status = AttendanceStatus::from($request->string('status')->toString());

        try {
            $service->record($session, $student, $status, 'dosen', $request->input('note'));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Status kehadiran tersimpan.');
    }

    /**
     * Mahasiswa melakukan presensi sendiri saat sesi terbuka.
     */
    public function selfCheck(SelfAttendanceRequest $request, CourseClass $courseClass, Meeting $meeting, AttendanceService $service): RedirectResponse
    {
        $this->assertBelongsToClass($courseClass, $meeting);

        $session = $this->resolveSession($meeting);

        $this->authorize('view', $session);

        $student = $request->user()->student;

        if (! $student) {
            return back()->withErrors(['student_id' => 'Data mahasiswa tidak ditemukan.']);
        }

        if (! $courseClass->enrollments()->where('student_id', $student->id)->where('status', 'aktif')->exists()) {
            return back()->withErrors(['student_id' => 'Anda tidak terdaftar aktif pada kelas ini.']);
        }

        try {
            $service->record($session, $student, AttendanceStatus::Present, 'self', $request->input('note'));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Presensi tercatat.');
    }

    /**
     * Koreksi status presensi oleh role berwenang (wajib alasan, tercatat di audit).
     */
    public function correct(AttendanceCorrectionRequest $request, CourseClass $courseClass, Meeting $meeting, Attendance $attendance, AttendanceService $service): RedirectResponse
    {
        $this->assertBelongsToClass($courseClass, $meeting);

        $session = $this->resolveSession($meeting);

        abort_unless($attendance->attendance_session_id === $session->id, 404);

        $this->authorize('correct', $attendance);

        $status = AttendanceStatus::from($request->string('status')->toString());

        try {
            $service->correct($attendance, $status, $request->string('reason')->toString(), $request->user());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Koreksi presensi tercatat.');
    }

    /**
     * Pastikan pertemuan memang milik kelas pada route (mencegah kebocoran antar kelas).
     */
    private function assertBelongsToClass(CourseClass $courseClass, Meeting $meeting): void
    {
        abort_unless((int) $meeting->course_class_id === (int) $courseClass->id, 404);
    }

    /**
     * Ambil sesi presensi milik pertemuan; buat bila belum ada dengan status "belum dimulai".
     *
     * Sesi yang baru dibuat tidak langsung terbuka: dosen/admin harus membukanya
     * secara eksplisit sebelum mahasiswa dapat melakukan presensi diri.
     */
    private function resolveSession(Meeting $meeting): AttendanceSession
    {
        $session = $meeting->attendanceSession;

        if (! $session) {
            $session = $meeting->attendanceSession()->create([
                'status' => AttendanceSessionStatus::NotStarted->value,
            ]);

            $meeting->setRelation('attendanceSession', $session);
        }

        return $session;
    }
}
