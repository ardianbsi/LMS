<?php

namespace App\Http\Controllers\MasterData;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StudentRequest;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $students = Student::query()
            ->with(['user', 'studyProgram'])
            ->when($request->string('q')->toString(), function ($query, $q) {
                $query->where('nim', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->when($request->integer('study_program_id'), fn ($query, $id) => $query->where('study_program_id', $id))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $programs = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('master-data.students.index', compact('students', 'programs'));
    }

    public function create(): View
    {
        $this->authorize('create', Student::class);

        $programs = StudyProgram::where('is_active', true)->orderBy('name')->get();

        return view('master-data.students.create', compact('programs'));
    }

    /**
     * Buat akun user (role student) + profil mahasiswa dalam satu transaksi.
     */
    public function store(StudentRequest $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('nim-'.$data['nim']),
                'role' => UserRole::Student,
            ]);

            $user->student()->create([
                'study_program_id' => $data['study_program_id'] ?? null,
                'nim' => $data['nim'],
                'entry_year' => $data['entry_year'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'] ?? 'aktif',
            ]);
        });

        return redirect()->route('master-data.students.index')->with('status', 'Mahasiswa berhasil ditambahkan.');
    }

    public function show(Student $student): View
    {
        $this->authorize('view', $student);

        $student->load(['user', 'studyProgram.faculty']);

        return view('master-data.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);

        $student->load('user');
        $programs = StudyProgram::orderBy('name')->get();

        return view('master-data.students.edit', compact('student', 'programs'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('update', $student);

        $data = $request->validated();

        DB::transaction(function () use ($data, $student) {
            $student->update([
                'study_program_id' => $data['study_program_id'] ?? $student->study_program_id,
                'nim' => $data['nim'],
                'entry_year' => $data['entry_year'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'] ?? $student->status,
            ]);

            $userData = [];
            if (! empty($data['name'])) {
                $userData['name'] = $data['name'];
            }
            if (! empty($data['email'])) {
                $userData['email'] = $data['email'];
            }
            if ($userData !== []) {
                $student->user()->update($userData);
            }
        });

        return redirect()->route('master-data.students.index')->with('status', 'Mahasiswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        // Profil dihapus; akun user ikut terhapus via cascade? Tidak — hapus eksplisit per kebijakan.
        // Di MVP: hapus profil + akun user (belum ada enrollment di Fase 2).
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->delete();
            $user?->delete();
        });

        return redirect()->route('master-data.students.index')->with('status', 'Mahasiswa berhasil dihapus.');
    }
}
