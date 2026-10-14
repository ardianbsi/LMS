<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\AcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AcademicYear::class);

        $years = AcademicYear::query()
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->string('status')->toString(), function ($query, $status) {
                $query->where('is_active', $status === 'aktif');
            })
            ->orderByDesc('start_date')
            ->paginate(15)
            ->withQueryString();

        return view('master-data.academic-years.index', compact('years'));
    }

    public function create(): View
    {
        $this->authorize('create', AcademicYear::class);

        return view('master-data.academic-years.create');
    }

    public function store(AcademicYearRequest $request): RedirectResponse
    {
        $this->authorize('create', AcademicYear::class);

        AcademicYear::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master-data.academic-years.index')->with('status', 'Tahun akademik berhasil ditambahkan.');
    }

    public function show(AcademicYear $academicYear): View
    {
        $this->authorize('view', $academicYear);

        $academicYear->load('semesters');

        return view('master-data.academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear): View
    {
        $this->authorize('update', $academicYear);

        return view('master-data.academic-years.edit', compact('academicYear'));
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('update', $academicYear);

        $academicYear->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master-data.academic-years.index')->with('status', 'Tahun akademik berhasil diperbarui.');
    }

    public function toggle(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('update', $academicYear);

        $academicYear->update(['is_active' => ! $academicYear->is_active]);

        return back()->with('status', $academicYear->is_active ? 'Tahun akademik diaktifkan.' : 'Tahun akademik dinonaktifkan.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('delete', $academicYear);

        if ($academicYear->isReferenced()) {
            return back()->withErrors(['academic_year' => 'Tahun akademik tidak dapat dihapus karena masih memiliki semester. Nonaktifkan saja.']);
        }

        $academicYear->delete();

        return redirect()->route('master-data.academic-years.index')->with('status', 'Tahun akademik berhasil dihapus.');
    }
}
