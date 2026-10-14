<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::AdminAcademic]);
    }

    public function test_admin_dapat_membuat_fakultas(): void
    {
        $response = $this->actingAs($this->admin())->post(route('master-data.faculties.store'), [
            'code' => 'FTI',
            'name' => 'Fakultas Teknologi Informasi',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('master-data.faculties.index'));
        $this->assertDatabaseHas('faculties', ['code' => 'FTI']);
    }

    public function test_kode_fakultas_harus_unik(): void
    {
        Faculty::factory()->create(['code' => 'FTI']);

        $response = $this->actingAs($this->admin())->post(route('master-data.faculties.store'), [
            'code' => 'FTI',
            'name' => 'Duplikat',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertEquals(1, Faculty::where('code', 'FTI')->count());
    }

    public function test_fakultas_direferensikan_tidak_bisa_dihapus(): void
    {
        $faculty = Faculty::factory()->create();
        \App\Models\StudyProgram::factory()->create(['faculty_id' => $faculty->id]);

        $response = $this->actingAs($this->admin())->delete(route('master-data.faculties.destroy', $faculty));

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('faculties', ['id' => $faculty->id]);
    }

    public function test_fakultas_bisa_dinonaktifkan(): void
    {
        $faculty = Faculty::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())->patch(route('master-data.faculties.toggle', $faculty));

        $this->assertDatabaseHas('faculties', ['id' => $faculty->id, 'is_active' => false]);
    }

    public function test_non_admin_ditolak_akses_crud(): void
    {
        $faculty = Faculty::factory()->create();

        foreach ([UserRole::Lecturer, UserRole::Student] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('master-data.faculties.index'))->assertForbidden();
            $this->actingAs($user)->post(route('master-data.faculties.store'), [
                'code' => 'X'.$role->value,
                'name' => 'Test',
            ])->assertForbidden();
            $this->actingAs($user)->delete(route('master-data.faculties.destroy', $faculty))->assertForbidden();
        }
    }

    public function test_search_dan_filter_status_bekerja(): void
    {
        Faculty::factory()->create(['code' => 'FTI', 'name' => 'Teknologi Informasi', 'is_active' => true]);
        Faculty::factory()->create(['code' => 'FEB', 'name' => 'Ekonomi Bisnis', 'is_active' => false]);

        $response = $this->actingAs($this->admin())->get(route('master-data.faculties.index', ['q' => 'Teknologi']));
        $response->assertOk();
        $response->assertSee('Teknologi Informasi');
        $response->assertDontSee('Ekonomi Bisnis');

        $response = $this->actingAs($this->admin())->get(route('master-data.faculties.index', ['status' => 'nonaktif']));
        $response->assertOk();
        $response->assertSee('Ekonomi Bisnis');
        $response->assertDontSee('Teknologi Informasi');
    }
}
