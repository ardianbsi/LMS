<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CourseClass;
use App\Models\User;

class CourseClassPolicy
{
    /**
     * Kelola penuh (CRUD, dosen, enrollment, jadwal): admin akademik (+ superadmin via Gate::before).
     */
    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::AdminAcademic);
    }

    public function viewAny(User $user): bool
    {
        return $this->manage($user) || $user->hasRole(UserRole::Lecturer, UserRole::Student);
    }

    public function view(User $user, CourseClass $courseClass): bool
    {
        if ($this->manage($user)) {
            return true;
        }

        if ($user->hasRole(UserRole::Lecturer) && $user->lecturer) {
            return $courseClass->classLecturers()->where('lecturer_id', $user->lecturer->id)->exists();
        }

        if ($user->hasRole(UserRole::Student) && $user->student) {
            return $courseClass->enrollments()->where('student_id', $user->student->id)->where('status', 'aktif')->exists();
        }

        return false;
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
