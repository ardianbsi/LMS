<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\CourseRequest;
use App\Models\Course;
use App\Models\StudyProgram;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Course::class);

        $courses = Course::query()
            ->with('studyProgram')
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->integer('study_program_id'), fn ($query, $id) => $query->where('study_program_id', $id))
            ->when($request->string('status')->toString(), function ($query, $status) {
                $query->where('courses.is_active', $status === 'aktif');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $programs = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('master-data.courses.index', compact('courses', 'programs'));
    }

    public function create(): View
    {
        $this->authorize('create', Course::class);

        $programs = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('master-data.courses.create', compact('programs'));
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        Course::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master-data.courses.index')->with('status', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Course $course): View
    {
        $this->authorize('view', $course);

        $course->load('studyProgram.faculty');

        return view('master-data.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $programs = StudyProgram::orderBy('name')->get();

        return view('master-data.courses.edit', compact('course', 'programs'));
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master-data.courses.index')->with('status', 'Mata kuliah berhasil diperbarui.');
    }

    public function toggle(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update(['is_active' => ! $course->is_active]);

        return back()->with('status', $course->is_active ? 'Mata kuliah diaktifkan.' : 'Mata kuliah dinonaktifkan.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        if ($course->isReferenced()) {
            return back()->withErrors(['course' => 'Mata kuliah tidak dapat dihapus karena masih direferensikan. Nonaktifkan saja.']);
        }

        $course->delete();

        return redirect()->route('master-data.courses.index')->with('status', 'Mata kuliah berhasil dihapus.');
    }
}
