<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_setiap_role_hanya_bisa_akses_dashboardnya(): void
    {
        $matrix = [
            [UserRole::Superadmin, '/dashboard/superadmin', 200],
            [UserRole::Superadmin, '/dashboard/admin-akademik', 200],
            [UserRole::AdminAcademic, '/dashboard/admin-akademik', 200],
            [UserRole::AdminAcademic, '/dashboard/superadmin', 403],
            [UserRole::Lecturer, '/dashboard/dosen', 200],
            [UserRole::Lecturer, '/dashboard/admin-akademik', 403],
            [UserRole::Lecturer, '/dashboard/mahasiswa', 403],
            [UserRole::Student, '/dashboard/mahasiswa', 200],
            [UserRole::Student, '/dashboard/dosen', 403],
            [UserRole::Student, '/dashboard/superadmin', 403],
        ];

        foreach ($matrix as [$role, $url, $status]) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get($url)->assertStatus($status);
        }
    }

    public function test_redirect_dashboard_sesuai_role(): void
    {
        $cases = [
            [UserRole::Superadmin, 'dashboard.superadmin'],
            [UserRole::AdminAcademic, 'dashboard.admin-academic'],
            [UserRole::Lecturer, 'dashboard.lecturer'],
            [UserRole::Student, 'dashboard.student'],
        ];

        foreach ($cases as [$role, $route]) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/dashboard')->assertRedirect(route($route));
        }
    }

    public function test_gates_dashboard_menolak_role_lain(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student]);

        $this->assertFalse($student->can('access-dashboard-lecturer'));
        $this->assertTrue($student->can('access-dashboard-student'));

        $superadmin = User::factory()->create(['role' => UserRole::Superadmin]);

        // Gate::before memberi akses penuh ke superadmin.
        $this->assertTrue($superadmin->can('access-dashboard-student'));
        $this->assertTrue($superadmin->can('access-admin-area'));
    }
}
