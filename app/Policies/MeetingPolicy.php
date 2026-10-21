<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CourseClass;
use App\Models\Meeting;
use App\Models\User;

/**
 * Otorisasi pertemuan (Fase 4).
 *
 * - Admin akademik: kelola penuh.
 * - Dosen: hanya kelas yang ditugaskan.
 * - Mahasiswa: hanya kelas yang diikuti.
 */
class MeetingPolicy
{
    /**
     * Admin akademik (+ superadmin via Gate::before).
     */
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::AdminAcademic);
    }

    /**
     * Dosen yang ditugaskan pada kelas tertentu.
     */
    public function teaches(User $user, CourseClass $courseClass): bool
    {
        return $user->hasRole(UserRole::Lecturer)
            && $user->lecturer !== null
            && $courseClass->classLecturers()->where('lecturer_id', $user->lecturer->id)->exists();
    }

    /**
     * Mahasiswa yang terdaftar aktif pada kelas tertentu.
     */
    public function enrolled(User $user, CourseClass $courseClass): bool
    {
        return $user->hasRole(UserRole::Student)
            && $user->student !== null
            && $courseClass->enrollments()->where('student_id', $user->student->id)->where('status', 'aktif')->exists();
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user)
            || $user->hasRole(UserRole::Lecturer, UserRole::Student);
    }

    public function view(User $user, Meeting $meeting): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        $courseClass = $meeting->courseClass;

        if (! $courseClass) {
            return false;
        }

        return $this->teaches($user, $courseClass) || $this->enrolled($user, $courseClass);
    }

    /**
     * Membuat pertemuan baru pada kelas (dosen pengampu atau admin).
     */
    public function create(User $user, CourseClass $courseClass): bool
    {
        return $this->manage($user) || $this->teaches($user, $courseClass);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        $courseClass = $meeting->courseClass;

        return $courseClass ? $this->teaches($user, $courseClass) : false;
    }

    /**
     * Hapus pertemuan; ditolak jika sudah ada data presensi di dalamnya.
     */
    public function delete(User $user, Meeting $meeting): bool
    {
        return $this->update($user, $meeting);
    }

    /**
     * Membuka/menutup sesi presensi pertemuan.
     */
    public function manageSession(User $user, Meeting $meeting): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        $courseClass = $meeting->courseClass;

        return $courseClass ? $this->teaches($user, $courseClass) : false;
    }

    /**
     * Mencatat/menandai kehadiran mahasiswa pada sesi pertemuan.
     */
    public function mark(User $user, Meeting $meeting): bool
    {
        return $this->manageSession($user, $meeting);
    }
}
