<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_dapat_ditampilkan(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Masuk', false);
    }

    public function test_user_dapat_login_dan_diarahkan_ke_dashboard_sesuai_role(): void
    {
        $cases = [
            ['superadmin@mycampus.local', UserRole::Superadmin, 'dashboard.superadmin'],
            ['admin@mycampus.local', UserRole::AdminAcademic, 'dashboard.admin-academic'],
            ['dosen@mycampus.local', UserRole::Lecturer, 'dashboard.lecturer'],
            ['mahasiswa@mycampus.local', UserRole::Student, 'dashboard.student'],
        ];

        foreach ($cases as [$email, $role, $route]) {
            User::factory()->create(['email' => $email, 'role' => $role]);

            $response = $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ]);

            $response->assertRedirect(route($route));
            $this->assertAuthenticated();
            $this->post('/logout');
        }
    }

    public function test_login_ditolak_dengan_kredensial_salah(): void
    {
        User::factory()->create(['email' => 'user@mycampus.local']);

        $response = $this->post('/login', [
            'email' => 'user@mycampus.local',
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_dapat_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_diarahkan_ke_login_saat_akses_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard/mahasiswa')->assertRedirect('/login');
    }
}
