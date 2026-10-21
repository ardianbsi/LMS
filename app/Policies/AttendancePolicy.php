<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\User;

/**
 * Otorisasi presensi (Fase 4).
 *
 * - Mahasiswa: presensi diri sendiri saat sesi terbuka.
 * - Dosen pengampu: buka/tutup sesi, catat & koreksi presensi kelasnya.
 * - Admin akademik: koreksi presensi seluruh kelas + rekap.
 */
class AttendancePolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::AdminAcademic);
    }

    /**
     * Dosen yang ditugaskan pada kelas pemilik sesi.
     */
    public function teaches(User $user, AttendanceSession $session): bool
    {
        $courseClass = $session->meeting?->courseClass;

        if (! $courseClass || ! $user->lecturer) {
            return false;
        }

        return $courseClass->classLecturers()->where('lecturer_id', $user->lecturer->id)->exists();
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user) || $user->hasRole(UserRole::Lecturer, UserRole::Student);
    }

    /**
     * Melihat daftar presensi sesi: dosen pengampu, admin, atau mahasiswa enrolled di kelas itu.
     */
    public function view(User $user, AttendanceSession $session): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        $courseClass = $session->meeting?->courseClass;

        if (! $courseClass) {
            return false;
        }

        if ($this->teaches($user, $session)) {
            return true;
        }

        return $user->hasRole(UserRole::Student)
            && $user->student !== null
            && $courseClass->enrollments()->where('student_id', $user->student->id)->where('status', 'aktif')->exists();
    }

    /**
     * Membuka/menutup sesi presensi.
     */
    public function manageSession(User $user, AttendanceSession $session): bool
    {
        return $this->manage($user) || $this->teaches($user, $session);
    }

    /**
     * Mencatat/menandai presence mahasiswa (oleh dosen).
     */
    public function mark(User $user, AttendanceSession $session): bool
    {
        return $this->manage($user) || $this->teaches($user, $session);
    }

    /**
     * Koreksi status presensi oleh role berwenang.
     */
    public function correct(User $user, Attendance $attendance): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        $session = $attendance->attendanceSession;

        return $session ? $this->teaches($user, $session) : false;
    }
}
