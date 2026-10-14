<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\FacultyRequest;
use App\Models\Faculty;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Daftar fakultas dengan search, filter status, pagination.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Faculty::class);

        $faculties = Faculty::query()
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
            })
            ->when($request->string('status')->toString(), function ($query, $status) {
                $query->where('is_active', $status === 'aktif');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master-data.faculties.index', compact('faculties'));
    }

    public function create(): View
    {
        $this->authorize('create', Faculty::class);

        return view('master-data.faculties.create');
    }

    public function store(FacultyRequest $request): RedirectResponse
    {
        $this->authorize('create', Faculty::class);

        Faculty::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('master-data.faculties.index')->with('status', 'Fakultas berhasil ditambahkan.');
    }

    public function show(Faculty $faculty): View
    {
        $this->authorize('view', $faculty);

        $faculty->load('studyPrograms');

        return view('master-data.faculties.show', compact('faculty'));
    }

    public function edit(Faculty $faculty): View
    {
        $this->authorize('update', $faculty);

        return view('master-data.faculties.edit', compact('faculty'));
    }

    public function update(FacultyRequest $request, Faculty $faculty): RedirectResponse
    {
        $this->authorize('update', $faculty);

        $faculty->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('master-data.faculties.index')->with('status', 'Fakultas berhasil diperbarui.');
    }

    /**
     * Nonaktifkan (bukan hapus fisik) agar histori akademik terjaga.
     */
    public function toggle(Faculty $faculty): RedirectResponse
    {
        $this->authorize('update', $faculty);

        $faculty->update(['is_active' => ! $faculty->is_active]);

        return back()->with('status', $faculty->is_active ? 'Fakultas diaktifkan.' : 'Fakultas dinonaktifkan.');
    }

    /**
     * Hapus fisik hanya bila tidak direferensikan.
     */
    public function destroy(Faculty $faculty): RedirectResponse
    {
        $this->authorize('delete', $faculty);

        if ($faculty->isReferenced()) {
            return back()->withErrors(['faculty' => 'Fakultas tidak dapat dihapus karena masih memiliki program studi. Nonaktifkan saja.']);
        }

        $faculty->delete();

        return redirect()->route('master-data.faculties.index')->with('status', 'Fakultas berhasil dihapus.');
    }
}
