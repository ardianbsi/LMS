<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Cek apakah user memiliki salah satu role yang diberikan.
     */
    public function hasRole(UserRole|string ...$roles): bool
    {
        $current = $this->role instanceof UserRole
            ? $this->role->value
            : (string) $this->role;

        $needles = array_map(
            fn (UserRole|string $role) => $role instanceof UserRole ? $role->value : $role,
            $roles
        );

        return in_array($current, $needles, true);
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole(UserRole::Superadmin);
    }

    public function isAdminAcademic(): bool
    {
        return $this->hasRole(UserRole::AdminAcademic);
    }

    public function isLecturer(): bool
    {
        return $this->hasRole(UserRole::Lecturer);
    }

    public function isStudent(): bool
    {
        return $this->hasRole(UserRole::Student);
    }

    /**
     * Profil mahasiswa (satu user satu profil).
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Profil dosen (satu user satu profil).
     */
    public function lecturer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Lecturer::class);
    }
}

