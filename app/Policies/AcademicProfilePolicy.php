<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class AcademicProfilePolicy
{
    /**
     * Kelola profil mahasiswa/dosen: superadmin (via Gate::before) + admin akademik.
     * Dosen/mahasiswa TIDAK boleh CRUD profil — hanya baca profil sendiri via modul lain.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::AdminAcademic);
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user);
    }

    public function view(User $user): bool
    {
        return $this->manage($user);
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user): bool
    {
        return $this->manage($user);
    }
}
