<?php

namespace App\Http\Controllers\Classes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classes\ClassLecturerRequest;
use App\Models\ClassLecturer;
use App\Models\CourseClass;
use App\Models\Lecturer;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassLecturerController extends Controller
{
    use AuthorizesRequests;

    public function index(CourseClass $courseClass): View
    {
        $this->authorize('view', $courseClass);

        $courseClass->load('classLecturers.lecturer.user');
        $lecturers = Lecturer::with('user')->where('status', 'aktif')->orderByDesc('id')->get();

        return view('classes.lecturers', compact('courseClass', 'lecturers'));
    }

    /**
     * Tetapkan dosen; duplikat ditolak dengan pesan jelas.
     */
    public function store(ClassLecturerRequest $request, CourseClass $courseClass): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        $exists = ClassLecturer::where('course_class_id', $courseClass->id)
            ->where('lecturer_id', $request->integer('lecturer_id'))
            ->exists();

        if ($exists) {
            return back()->withErrors(['lecturer_id' => 'Dosen sudah ditugaskan di kelas ini.']);
        }

        ClassLecturer::create([
            'course_class_id' => $courseClass->id,
            'lecturer_id' => $request->integer('lecturer_id'),
            'role' => $request->input('role', 'pengampu'),
        ]);

        return back()->with('status', 'Dosen berhasil ditugaskan.');
    }

    public function destroy(CourseClass $courseClass, ClassLecturer $lecturer): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        if ($lecturer->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $lecturer->delete();

        return back()->with('status', 'Penugasan dosen dihapus.');
    }
}
