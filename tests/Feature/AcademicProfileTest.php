<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicProfileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::AdminAcademic]);
    }

    public function test_admin_dapat_membuat_mahasiswa_dengan_akun(): void
    {
        $program = StudyProgram::factory()->create();

        $response = $this->actingAs($this->admin())->post(route('master-data.students.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@mycampus.local',
            'study_program_id' => $program->id,
            'nim' => '20250001',
            'entry_year' => 2025,
        ]);

        $response->assertRedirect(route('master-data.students.index'));

        $user = User::where('email', 'budi@mycampus.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(UserRole::Student));

        $student = Student::where('nim', '20250001')->first();
        $this->assertNotNull($student);
        $this->assertEquals($user->id, $student->user_id);
        $this->assertEquals('Budi Santoso', $student->displayName());
    }

    public function test_nim_harus_unik(): void
    {
        Student::factory()->create(['nim' => '20250001']);

        $response = $this->actingAs($this->admin())->post(route('master-data.students.store'), [
            'name' => 'Duplikat',
            'email' => 'duplikat@mycampus.local',
            'nim' => '20250001',
            'entry_year' => 2025,
        ]);

        $response->assertSessionHasErrors('nim');
        $this->assertEquals(1, Student::where('nim', '20250001')->count());
    }

    public function test_admin_dapat_membuat_dosen_dengan_akun(): void
    {
        $program = StudyProgram::factory()->create();

        $response = $this->actingAs($this->admin())->post(route('master-data.lecturers.store'), [
            'name' => 'Dr. Siti',
            'email' => 'siti@mycampus.local',
            'study_program_id' => $program->id,
            'nip' => '198001012010012001',
            'academic_title' => 'Dr.',
        ]);

        $response->assertRedirect(route('master-data.lecturers.index'));

        $user = User::where('email', 'siti@mycampus.local')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(UserRole::Lecturer));

        $lecturer = Lecturer::where('nip', '198001012010012001')->first();
        $this->assertNotNull($lecturer);
        $this->assertEquals($user->id, $lecturer->user_id);
    }

    public function test_nip_harus_unik(): void
    {
        Lecturer::factory()->create(['nip' => '198001012010012001']);

        $response = $this->actingAs($this->admin())->post(route('master-data.lecturers.store'), [
            'name' => 'Duplikat',
            'email' => 'duplikat@mycampus.local',
            'nip' => '198001012010012001',
        ]);

        $response->assertSessionHasErrors('nip');
        $this->assertEquals(1, Lecturer::where('nip', '198001012010012001')->count());
    }

    public function test_mahasiswa_baru_bisa_login(): void
    {
        $this->actingAs($this->admin())->post(route('master-data.students.store'), [
            'name' => 'Ani',
            'email' => 'ani@mycampus.local',
            'nim' => '20250002',
            'entry_year' => 2025,
        ]);

        $this->post('/logout');

        $response = $this->post('/login', [
            'email' => 'ani@mycampus.local',
            'password' => 'nim-20250002',
        ]);

        $response->assertRedirect(route('dashboard.student'));
        $this->assertAuthenticated();
    }
}
