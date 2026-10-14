<?php

namespace App\Http\Controllers\MasterData;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\LecturerRequest;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LecturerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lecturer::class);

        $lecturers = Lecturer::query()
            ->with(['user', 'studyProgram'])
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where('nip', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->when($request->integer('study_program_id'), fn ($query, $id) => $query->where('study_program_id', $id))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $programs = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('master-data.lecturers.index', compact('lecturers', 'programs'));
    }

    public function create(): View
    {
        $this->authorize('create', Lecturer::class);

        $programs = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('master-data.lecturers.create', compact('programs'));
    }

    /**
     * Buat akun user (role lecturer) + profil dosen dalam satu transaksi.
     */
    public function store(LecturerRequest $request): RedirectResponse
    {
        $this->authorize('create', Lecturer::class);

        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('nip-'.$data['nip']),
                'role' => UserRole::Lecturer,
            ]);

            $user->lecturer()->create([
                'study_program_id' => $data['study_program_id'] ?? null,
                'nip' => $data['nip'],
                'academic_title' => $data['academic_title'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'aktif',
            ]);
        });

        return redirect()->route('master-data.lecturers.index')->with('status', 'Dosen berhasil ditambahkan.');
    }

    public function show(Lecturer $lecturer): View
    {
        $this->authorize('view', $lecturer);

        $lecturer->load(['user', 'studyProgram.faculty']);

        return view('master-data.lecturers.show', compact('lecturer'));
    }

    public function edit(Lecturer $lecturer): View
    {
        $this->authorize('update', $lecturer);

        $lecturer->load('user');
        $programs = StudyProgram::orderBy('name')->get();

        return view('master-data.lecturers.edit', compact('lecturer', 'programs'));
    }

    public function update(LecturerRequest $request, Lecturer $lecturer): RedirectResponse
    {
        $this->authorize('update', $lecturer);

        $data = $request->validated();

        DB::transaction(function () use ($data, $lecturer) {
            $lecturer->update([
                'study_program_id' => $data['study_program_id'] ?? $lecturer->study_program_id,
                'nip' => $data['nip'],
                'academic_title' => $data['academic_title'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? $lecturer->status,
            ]);

            $userData = [];
            if (! empty($data['name'])) {
                $userData['name'] = $data['name'];
            }
            if (! empty($data['email'])) {
                $userData['email'] = $data['email'];
            }
            if ($userData !== []) {
                $lecturer->user()->update($userData);
            }
        });

        return redirect()->route('master-data.lecturers.index')->with('status', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Lecturer $lecturer): RedirectResponse
    {
        $this->authorize('delete', $lecturer);

        DB::transaction(function () use ($lecturer) {
            $user = $lecturer->user;
            $lecturer->delete();
            $user?->delete();
        });

        return redirect()->route('master-data.lecturers.index')->with('status', 'Dosen berhasil dihapus.');
    }
}
