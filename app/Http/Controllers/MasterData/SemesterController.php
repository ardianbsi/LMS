<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\SemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Semester::class);

        $semesters = Semester::query()
            ->with('academicYear')
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(fn ($w) => $w->where('term_code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->integer('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
            ->when($request->string('status')->toString(), function ($query, $status) {
                $query->where('semesters.is_active', $status === 'aktif');
            })
            ->orderByDesc('start_date')
            ->paginate(15)
            ->withQueryString();

        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('master-data.semesters.index', compact('semesters', 'years'));
    }

    public function create(): View
    {
        $this->authorize('create', Semester::class);

        $years = AcademicYear::where('is_active', true)->orderByDesc('start_date')->get();

        return view('master-data.semesters.create', compact('years'));
    }

    public function store(SemesterRequest $request): RedirectResponse
    {
        $this->authorize('create', Semester::class);

        Semester::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master-data.semesters.index')->with('status', 'Semester berhasil ditambahkan.');
    }

    public function show(Semester $semester): View
    {
        $this->authorize('view', $semester);

        $semester->load('academicYear');

        return view('master-data.semesters.show', compact('semester'));
    }

    public function edit(Semester $semester): View
    {
        $this->authorize('update', $semester);

        $years = AcademicYear::orderByDesc('start_date')->get();

        return view('master-data.semesters.edit', compact('semester', 'years'));
    }

    public function update(SemesterRequest $request, Semester $semester): RedirectResponse
    {
        $this->authorize('update', $semester);

        $semester->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master-data.semesters.index')->with('status', 'Semester berhasil diperbarui.');
    }

    public function toggle(Semester $semester): RedirectResponse
    {
        $this->authorize('update', $semester);

        $semester->update(['is_active' => ! $semester->is_active]);

        return back()->with('status', $semester->is_active ? 'Semester diaktifkan.' : 'Semester dinonaktifkan.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        $this->authorize('delete', $semester);

        if ($semester->isReferenced()) {
            return back()->withErrors(['semester' => 'Semester tidak dapat dihapus karena masih direferensikan. Nonaktifkan saja.']);
        }

        $semester->delete();

        return redirect()->route('master-data.semesters.index')->with('status', 'Semester berhasil dihapus.');
    }
}
