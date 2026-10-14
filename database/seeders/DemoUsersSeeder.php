<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder demo HANYA untuk development/testing.
 * Jangan jalankan di production.
 */
class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('DemoUsersSeeder dilewati: environment production.');

            return;
        }

        $users = [
            ['Superadmin', 'superadmin@mycampus.local', UserRole::Superadmin],
            ['Admin Akademik', 'admin.akademik@mycampus.local', UserRole::AdminAcademic],
            ['Dosen Demo', 'dosen@mycampus.local', UserRole::Lecturer],
            ['Mahasiswa Demo', 'mahasiswa@mycampus.local', UserRole::Student],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command->info('Akun demo dibuat (password: password). HANYA untuk dev/testing.');
    }
}
