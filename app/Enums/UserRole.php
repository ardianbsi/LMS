<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case AdminAcademic = 'admin_academic';
    case Lecturer = 'lecturer';
    case Student = 'student';

    /**
     * Daftar role yang valid untuk validasi.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Label Bahasa Indonesia untuk tampilan.
     */
    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::AdminAcademic => 'Admin Akademik',
            self::Lecturer => 'Dosen',
            self::Student => 'Mahasiswa',
        };
    }

    /**
     * Dashboard tujuan setelah login.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Superadmin => 'dashboard.superadmin',
            self::AdminAcademic => 'dashboard.admin-academic',
            self::Lecturer => 'dashboard.lecturer',
            self::Student => 'dashboard.student',
        };
    }
}
