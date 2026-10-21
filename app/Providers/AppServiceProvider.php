<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Superadmin boleh melakukan apapun (default allow).
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperadmin()) {
                return true;
            }

            return null;
        });

        // Akses dashboard per role.
        foreach (UserRole::cases() as $role) {
            Gate::define("access-dashboard-{$role->value}", fn (User $user) => $user->hasRole($role));
        }

        // Area admin: superadmin (via before) + admin akademik.
        Gate::define('access-admin-area', fn (User $user) => $user->hasRole(UserRole::AdminAcademic));

        // Area pengajar: dosen (dan admin via kebutuhan operasional dibatasi policy modul).
        Gate::define('access-lecturer-area', fn (User $user) => $user->hasRole(UserRole::Lecturer));

        // Area mahasiswa.
        Gate::define('access-student-area', fn (User $user) => $user->hasRole(UserRole::Student));

        // Rekap presensi per semester: admin akademik & dosen.
        Gate::define('view-semester-attendance-recap', fn (User $user) => $user->hasRole(UserRole::AdminAcademic, UserRole::Lecturer));

        // Master data akademik: admin-only via policy.
        Gate::policy(\App\Models\Faculty::class, \App\Policies\MasterDataPolicy::class);
        Gate::policy(\App\Models\StudyProgram::class, \App\Policies\MasterDataPolicy::class);
        Gate::policy(\App\Models\AcademicYear::class, \App\Policies\MasterDataPolicy::class);
        Gate::policy(\App\Models\Semester::class, \App\Policies\MasterDataPolicy::class);
        Gate::policy(\App\Models\Course::class, \App\Policies\MasterDataPolicy::class);
        Gate::policy(\App\Models\Room::class, \App\Policies\MasterDataPolicy::class);
        Gate::policy(\App\Models\Student::class, \App\Policies\AcademicProfilePolicy::class);
        Gate::policy(\App\Models\Lecturer::class, \App\Policies\AcademicProfilePolicy::class);

        // Kelas & jadwal: admin kelola penuh; dosen/mahasiswa baca kelasnya via policy.
        Gate::policy(\App\Models\CourseClass::class, \App\Policies\CourseClassPolicy::class);

        // Pertemuan & presensi (Fase 4).
        Gate::policy(\App\Models\Meeting::class, \App\Policies\MeetingPolicy::class);
        Gate::policy(\App\Models\AttendanceSession::class, \App\Policies\AttendancePolicy::class);
        Gate::policy(\App\Models\Attendance::class, \App\Policies\AttendancePolicy::class);
    }
}

