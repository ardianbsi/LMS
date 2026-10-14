<?php

namespace App\Http\Controllers\Classes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classes\EnrollmentRequest;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\ScheduleConflictService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    use AuthorizesRequests;

    public function index(CourseClass $courseClass): View
    {
        $this->authorize('view', $courseClass);

        $courseClass->load('enrollments.student.user');

        return view('classes.enrollments', compact('courseClass'));
    }

    /**
     * Daftarkan mahasiswa; duplikat + bentrok jadwal ditolak dengan pesan jelas.
     */
    public function store(EnrollmentRequest $request, CourseClass $courseClass, ScheduleConflictService $conflicts): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        $studentId = $request->integer('student_id');

        $exists = Enrollment::where('course_class_id', $courseClass->id)
            ->where('student_id', $studentId)
            ->exists();

        if ($exists) {
            return back()->withErrors(['student_id' => 'Mahasiswa sudah terdaftar di kelas ini.']);
        }

        $studentConflicts = $conflicts->checkStudentEnrollment($studentId, $courseClass->load('schedules.room'));

        if ($studentConflicts !== []) {
            return back()->withErrors(['student_id' => $studentConflicts[0]['message']]);
        }

        $courseClass->enrollments()->create([
            'student_id' => $studentId,
            'status' => $request->input('status', 'aktif'),
        ]);

        return back()->with('status', 'Mahasiswa berhasil didaftarkan.');
    }

    public function destroy(CourseClass $courseClass, Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        if ($enrollment->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $enrollment->delete();

        return back()->with('status', 'Enrollment dihapus.');
    }

    /**
     * Form tambah menampilkan mahasiswa aktif yang belum terdaftar di kelas ini.
     */
    public function candidates(CourseClass $courseClass): View
    {
        $this->authorize('update', $courseClass);

        $enrolledIds = $courseClass->enrollments()->pluck('student_id')->all();

        $students = Student::with('user')
            ->where('status', 'aktif')
            ->whereNotIn('id', $enrolledIds)
            ->orderByDesc('id')
            ->paginate(20);

        return view('classes.enrollment-add', compact('courseClass', 'students'));
    }
}
