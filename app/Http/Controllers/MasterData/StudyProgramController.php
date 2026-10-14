<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StudyProgramRequest;
use App\Models\Faculty;
use App\Models\StudyProgram;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudyProgramController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudyProgram::class);

        $programs = StudyProgram::query()
            ->with('faculty')
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->integer('faculty_id'), fn ($query, $id) => $query->where('faculty_id', $id))
            ->when($request->string('status')->toString(), function ($query, $status) {
                $query->where('study_programs.is_active', $status === 'aktif');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $faculties = Faculty::where('is_active', true)->orderBy('name')->get();

        return view('master-data.study-programs.index', compact('programs', 'faculties'));
    }

    public function create(): View
    {
        $this->authorize('create', StudyProgram::class);

        $faculties = Faculty::where('is_active', true)->orderBy('name')->get();

        return view('master-data.study-programs.create', compact('faculties'));
    }

    public function store(StudyProgramRequest $request): RedirectResponse
    {
        $this->authorize('create', StudyProgram::class);

        StudyProgram::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master-data.study-programs.index')->with('status', 'Program studi berhasil ditambahkan.');
    }

    public function show(StudyProgram $studyProgram): View
    {
        $this->authorize('view', $studyProgram);

        $studyProgram->load(['faculty', 'courses']);

        return view('master-data.study-programs.show', compact('studyProgram'));
    }

    public function edit(StudyProgram $studyProgram): View
    {
        $this->authorize('update', $studyProgram);

        $faculties = Faculty::orderBy('name')->get();

        return view('master-data.study-programs.edit', compact('studyProgram', 'faculties'));
    }

    public function update(StudyProgramRequest $request, StudyProgram $studyProgram): RedirectResponse
    {
        $this->authorize('update', $studyProgram);

        $studyProgram->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master-data.study-programs.index')->with('status', 'Program studi berhasil diperbarui.');
    }

    public function toggle(StudyProgram $studyProgram): RedirectResponse
    {
        $this->authorize('update', $studyProgram);

        $studyProgram->update(['is_active' => ! $studyProgram->is_active]);

        return back()->with('status', $studyProgram->is_active ? 'Program studi diaktifkan.' : 'Program studi dinonaktifkan.');
    }

    public function destroy(StudyProgram $studyProgram): RedirectResponse
    {
        $this->authorize('delete', $studyProgram);

        if ($studyProgram->isReferenced()) {
            return back()->withErrors(['study_program' => 'Prodi tidak dapat dihapus karena masih direferensikan. Nonaktifkan saja.']);
        }

        $studyProgram->delete();

        return redirect()->route('master-data.study-programs.index')->with('status', 'Program studi berhasil dihapus.');
    }
}
