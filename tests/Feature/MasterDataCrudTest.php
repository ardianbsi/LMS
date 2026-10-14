<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Room;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::AdminAcademic]);
    }

    public function test_crud_program_studi_dengan_relasi_fakultas(): void
    {
        $admin = $this->admin();

        // Create.
        $this->actingAs($admin)->post(route('master-data.study-programs.store'), [
            'faculty_id' => \App\Models\Faculty::factory()->create()->id,
            'code' => 'SI',
            'name' => 'Sistem Informasi',
            'degree' => 'S1',
        ])->assertRedirect(route('master-data.study-programs.index'));

        $program = StudyProgram::where('code', 'SI')->first();
        $this->assertNotNull($program);
        $this->assertNotNull($program->faculty);

        // Unique constraint.
        $this->actingAs($admin)->post(route('master-data.study-programs.store'), [
            'faculty_id' => $program->faculty_id,
            'code' => 'SI',
            'name' => 'Duplikat',
        ])->assertSessionHasErrors('code');

        // FK konsisten: faculty_id tidak valid ditolak.
        $this->actingAs($admin)->post(route('master-data.study-programs.store'), [
            'faculty_id' => 999999,
            'code' => 'XX',
            'name' => 'Test',
        ])->assertSessionHasErrors('faculty_id');

        // Update.
        $this->actingAs($admin)->put(route('master-data.study-programs.update', $program), [
            'faculty_id' => $program->faculty_id,
            'code' => 'SI',
            'name' => 'Sistem Informasi Updated',
            'is_active' => true,
        ])->assertRedirect(route('master-data.study-programs.index'));

        $this->assertDatabaseHas('study_programs', ['id' => $program->id, 'name' => 'Sistem Informasi Updated']);
    }

    public function test_crud_tahun_akademik_dan_semester_dengan_term_code_unik(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('master-data.academic-years.store'), [
            'code' => '2025/2026',
            'name' => 'Tahun Akademik 2025/2026',
            'start_date' => '2025-08-01',
            'end_date' => '2026-07-31',
        ])->assertRedirect(route('master-data.academic-years.index'));

        $year = AcademicYear::where('code', '2025/2026')->first();
        $this->assertNotNull($year);

        // Tanggal selesai harus setelah mulai.
        $this->actingAs($admin)->post(route('master-data.academic-years.store'), [
            'code' => '2026/2027',
            'name' => 'Invalid',
            'start_date' => '2026-08-01',
            'end_date' => '2026-07-31',
        ])->assertSessionHasErrors('end_date');

        // Semester dengan term_code unik.
        $this->actingAs($admin)->post(route('master-data.semesters.store'), [
            'academic_year_id' => $year->id,
            'term_code' => '20251',
            'name' => 'Ganjil',
            'start_date' => '2025-08-01',
            'end_date' => '2026-01-31',
        ])->assertRedirect(route('master-data.semesters.index'));

        $this->actingAs($admin)->post(route('master-data.semesters.store'), [
            'academic_year_id' => $year->id,
            'term_code' => '20251',
            'name' => 'Duplikat',
            'start_date' => '2025-08-01',
            'end_date' => '2026-01-31',
        ])->assertSessionHasErrors('term_code');

        $semester = Semester::where('term_code', '20251')->first();
        $this->assertEquals($year->id, $semester->academicYear->id);
    }

    public function test_crud_mata_kuliah_dan_ruang_dengan_kode_unik(): void
    {
        $admin = $this->admin();
        $program = StudyProgram::factory()->create();

        $this->actingAs($admin)->post(route('master-data.courses.store'), [
            'study_program_id' => $program->id,
            'code' => 'MK101',
            'name' => 'Algoritma',
            'credits' => 3,
        ])->assertRedirect(route('master-data.courses.index'));

        $this->actingAs($admin)->post(route('master-data.courses.store'), [
            'study_program_id' => $program->id,
            'code' => 'MK101',
            'name' => 'Duplikat',
            'credits' => 2,
        ])->assertSessionHasErrors('code');

        $this->assertEquals($program->id, Course::where('code', 'MK101')->first()->studyProgram->id);

        $this->actingAs($admin)->post(route('master-data.rooms.store'), [
            'code' => 'R-101',
            'name' => 'Ruang 101',
            'capacity' => 40,
        ])->assertRedirect(route('master-data.rooms.index'));

        $this->actingAs($admin)->post(route('master-data.rooms.store'), [
            'code' => 'R-101',
            'name' => 'Duplikat',
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseHas('rooms', ['code' => 'R-101', 'capacity' => 40]);
    }

    public function test_non_admin_ditolak_semua_crud_master_data(): void
    {
        $routes = [
            'master-data.study-programs.index',
            'master-data.academic-years.index',
            'master-data.semesters.index',
            'master-data.courses.index',
            'master-data.rooms.index',
            'master-data.students.index',
            'master-data.lecturers.index',
        ];

        $student = User::factory()->create(['role' => UserRole::Student]);
        $lecturer = User::factory()->create(['role' => UserRole::Lecturer]);

        foreach ($routes as $route) {
            $this->actingAs($student)->get(route($route))->assertForbidden();
            $this->actingAs($lecturer)->get(route($route))->assertForbidden();
        }
    }
}
