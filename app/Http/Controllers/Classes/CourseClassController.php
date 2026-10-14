<?php

namespace App\Http\Controllers\Classes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classes\CourseClassRequest;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Semester;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseClassController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CourseClass::class);

        $user = $request->user();

        $classes = CourseClass::query()
            ->with(['course.studyProgram', 'semester.academicYear', 'schedules.room', 'lecturers'])
            ->withCount(['enrollments', 'schedules'])
            // Scope dosen: hanya kelas yang ditugaskan.
            ->when($user->hasRole('lecturer') && $user->lecturer, function ($query) use ($user) {
                $query->whereHas('classLecturers', fn ($q) => $q->where('lecturer_id', $user->lecturer->id));
            })
            // Scope mahasiswa: hanya kelas yang diikuti.
            ->when($user->hasRole('student') && $user->student, function ($query) use ($user) {
                $query->whereHas('enrollments', fn ($q) => $q->where('student_id', $user->student->id)->where('status', 'aktif'));
            })
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where('class_code', 'like', "%{$q}%")
                    ->orWhereHas('course', fn ($c) => $c->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->integer('semester_id'), fn ($query, $id) => $query->where('semester_id', $id))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('course_classes.status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $semesters = Semester::with('academicYear')->orderByDesc('start_date')->get();

        return view('classes.index', compact('classes', 'semesters'));
    }

    public function create(): View
    {
        $this->authorize('create', CourseClass::class);

        $courses = Course::where('is_active', true)->orderBy('name')->get();
        $semesters = Semester::where('is_active', true)->orderByDesc('start_date')->get();

        return view('classes.create', compact('courses', 'semesters'));
    }

    public function store(CourseClassRequest $request): RedirectResponse
    {
        $this->authorize('create', CourseClass::class);

        CourseClass::create($request->validated() + ['status' => $request->input('status', 'aktif')]);

        return redirect()->route('classes.index')->with('status', 'Kelas berhasil dibuat.');
    }

    public function show(CourseClass $courseClass): View
    {
        $this->authorize('view', $courseClass);

        $courseClass->load([
            'course.studyProgram.faculty',
            'semester.academicYear',
            'schedules.room',
            'classLecturers.lecturer.user',
            'enrollments.student.user',
        ]);

        return view('classes.show', compact('courseClass'));
    }

    public function edit(CourseClass $courseClass): View
    {
        $this->authorize('update', $courseClass);

        $courses = Course::orderBy('name')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        return view('classes.edit', compact('courseClass', 'courses', 'semesters'));
    }

    public function update(CourseClassRequest $request, CourseClass $courseClass): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        $courseClass->update($request->validated() + ['status' => $request->input('status', $courseClass->status)]);

        return redirect()->route('classes.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(CourseClass $courseClass): RedirectResponse
    {
        $this->authorize('delete', $courseClass);

        if ($courseClass->enrollments()->exists() || $courseClass->schedules()->exists()) {
            return back()->withErrors(['class' => 'Kelas tidak dapat dihapus karena masih memiliki enrollment/jadwal. Nonaktifkan statusnya.']);
        }

        $courseClass->classLecturers()->delete();
        $courseClass->delete();

        return redirect()->route('classes.index')->with('status', 'Kelas berhasil dihapus.');
    }
}
