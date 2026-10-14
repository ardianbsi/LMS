# HANDOFF STATE — MyCampus LMS

> AI coding agent: perbarui dokumen ini di akhir setiap fase. Jangan mengklaim pekerjaan selesai tanpa verifikasi.

## Current status
- Fase terakhir: Fase 2 — Master Data Akademik
- Status: Complete
- Tanggal: 2026-10-14 (UTC)
- Branch: `fase-2-master-data`

## Repository baseline
- Laravel: 13.x (`laravel/framework ^13.17`, skeleton fresh)
- PHP: 8.3.35 (terverifikasi via `php -v`)
- Database: SQLite untuk dev (`.env.example`), koneksi `pgsql` terdefinisi untuk target PRD
- UI stack: Blade + TailwindCSS v4 + Vite (tanpa Livewire/Breeze — auth Blade manual)
- Test framework: PHPUnit 12.5 (`php artisan test`, sqlite `:memory:`)
- Auth/permission: tanpa package eksternal — `UserRole` enum + middleware `role` + Gates

## Completed
- [x] Fase 0 — Audit (read-only, tanpa perubahan)
- [x] Fase 1 — Fondasi/Auth/UI
- [x] Fase 2 — Master data
- [ ] Fase 3 — Kelas/Enrollment/Jadwal
- [ ] Fase 4 — Pertemuan/Presensi
- [ ] Fase 5 — Materi
- [ ] Fase 6 — Diskusi
- [ ] Fase 7 — Tugas/Submission/Nilai
- [ ] Fase 8 — Pengumuman/Laporan
- [ ] Fase 9 — Kuliah pengganti
- [ ] Fase 10 — Hardening/Deployment

## Files changed
- `config/app.php`: timezone `Asia/Jakarta`, locale `id`, faker `id_ID`
- `.env.example`: `APP_NAME="MyCampus LMS"`, locale `id`
- `app/Enums/UserRole.php` (baru): enum 4 role + label BI + dashboardRoute
- `app/Models/User.php`: fillable role, cast enum, helper hasRole/isX
- `app/Http/Middleware/EnsureUserHasRole.php` (baru) + alias `role` di `bootstrap/app.php`
- `app/Providers/AppServiceProvider.php`: Gate::before superadmin + Gates dashboard-area
- `app/Http/Requests/Auth/LoginRequest.php`, `Controllers/Auth/*`, `Controllers/DashboardController.php` (baru)
- `routes/web.php`: guest (login/lupa/reset) + grup auth (logout, 4 dashboard role-protected)
- `resources/views/layouts/{guest,app}.blade.php`, `auth/*`, `dashboard/*` (baru, BI, responsif)
- `database/migrations/2026_10_14_000001_add_role_to_users_table.php` (baru)
- `database/factories/UserFactory.php`, `seeders/DemoUsersSeeder.php` (dev-only)
- `tests/TestCase.php` (withoutVite), `tests/Feature/{Authentication,RoleAccess,PasswordReset}Test.php` (baru)

### Fase 2 — Master Data Akademik (branch `fase-2-master-data`)
- Migrasi: `2026_10_14_000002_create_academic_master_tables` (faculties, study_programs, academic_years, semesters, courses, rooms — FK restrictOnDelete, unique code/term_code, is_active) + `2026_10_14_000003_create_student_lecturer_profiles` (students: user unique cascade + NIM unique; lecturers: user unique cascade + NIP unique)
- 8 model: Faculty, StudyProgram, AcademicYear, Semester, Course, Room, Student, Lecturer (+ relasi, casts, `isReferenced()`, `displayName()`) + relasi `User::student()/lecturer()`
- 8 factories master data (Student/Lecturer factory membuat User role sesuai)
- Policies: `MasterDataPolicy` + `AcademicProfilePolicy` (admin-only; superadmin via Gate::before), didaftarkan via `Gate::policy()` x8
- 8 Form Requests validasi BI + unique-ignore + pesan Indonesia
- 8 Controllers CRUD: search/filter/pagination, toggle nonaktif (6 entitas umum), destroy guard `isReferenced()`, Student/Lecturer buat akun User via transaksi DB
- Routes: grup `master-data` prefix + middleware `role:superadmin,admin_academic`, 8 resource + 6 toggle
- Views: `_nav` + 32 view (index/create/edit/show x8) BI responsif + link Master Data di nav admin
- Tests: `FacultyCrudTest` (6), `MasterDataCrudTest` (4), `AcademicProfileTest` (5) — 15 tests baru


## Database changes
- Migration dibuat: `2026_10_14_000001_add_role_to_users_table` (kolom role default student + index)
- Dijalankan pada: sqlite `:memory:` via RefreshDatabase saat test (13 tests lolos termasuk skema role)
- Seeder: `DemoUsersSeeder` (4 akun demo, password `password`, dilewati di production)
- Rollback: `down()` drop index + kolom — aman, non-destruktif

### Fase 2
- Migration dibuat: `2026_10_14_000002_create_academic_master_tables`, `2026_10_14_000003_create_student_lecturer_profiles`
- Dijalankan pada: sqlite `:memory:` via RefreshDatabase saat test (28 tests lolos termasuk skema + FK + unique)
- Rollback: `down()` drop tabel urutan terbalik — aman, non-destruktif

## Tests
- Command: `php artisan test` (JUnit: `storage/logs/fase1-test2.xml`, dibersihkan setelah verifikasi)
- Hasil aktual: 13 tests, 52 assertions, 0 failures, 0 errors
- Run pertama 3 gagal (`ViteManifestNotFoundException`); diperbaiki via `withoutVite()` di TestCase
- `npm run build` tidak dijalankan (node/npm belum terverifikasi) — known issue visual saja

### Fase 2
- Command: `php artisan test` (JUnit: `storage/logs/fase2-test2.xml`, dibersihkan setelah verifikasi)
- Hasil aktual: 28 tests, 140 assertions, 0 failures, 0 errors (13 lama + 15 baru)
- Run pertama 27/28: 1 gagal karena bug `StudyProgramRequest` membaca `route('study_program')` padahal parameter `studyProgram` → unique-ignore gagal; diperbaiki (juga `AcademicYearRequest`), run kedua 100% lulus
- Cakupan baru: CRUD fakultas/prodi/tahun/semester/MK/ruang, unique constraint (kode/term/NIM/NIP), destroy guard referensi, toggle nonaktif, search/filter, non-admin 403 (7 route x 2 role), mahasiswa baru bisa login

## Decisions
- Paket A: SQLite dev + role native + Blade manual, tanpa package baru (hindari risiko Laravel 13)
- `laravel/boost --dev` belum diinstall (butuh izin eksplisit install dependency)
- Dashboard orisinal (Matrix Admin tidak tersedia di repo)
- Rate limit login 5/menit/IP; reset 5/menit; superadmin Gate::before allow-all

### Fase 2
- Nama/email tidak diduplikasi di students/lecturers — diambil dari relasi `user`
- Password awal mahasiswa `nim-<NIM>`, dosen `nip-<NIP>` (wajib diganti setelah login; reset tersedia)
- Data direferensikan tidak dihapus fisik — nonaktifkan; destroy ditolak dengan pesan
- Student/Lecturer destroy menghapus profil + akun user (belum ada enrollment; guard enrollment ditambah Fase 3)
- `Semester/Course/Room::isReferenced()` return false sementara — diisi referensi nyata di Fase 3

## Known issues / blockers
- Assets Vite belum di-build — halaman render tanpa CSS/JS sampai `npm run build`
- Shell integration bermasalah (exit code 1 meski sukses) — diverifikasi via output + JUnit + git log
- `.env` aktual tidak disentuh; reset email pakai mailer log/array (belum uji kirim asli)

## Next exact step
- Fase berikutnya: Fase 3 — Kelas, Dosen Pengampu, Enrollment, Jadwal
- Task terkecil: migrasi + model `course_classes, class_lecturers, enrollments, class_schedules` + service deteksi bentrok ruang/dosen/mahasiswa (server-side)
- File PRD: `01_MASTER_SPEC.txt`, `05_FASE_03_KELAS_ENROLLMENT_JADWAL.txt`, `HANDOFF_STATE.md`
