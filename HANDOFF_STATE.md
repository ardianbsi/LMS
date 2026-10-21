# HANDOFF STATE — MyCampus LMS

> AI coding agent: perbarui dokumen ini di akhir setiap fase. Jangan mengklaim pekerjaan selesai tanpa verifikasi.

## Current status
- Fase terakhir: Fase 3 — Kelas, Dosen Pengampu, Enrollment, Jadwal
- Status: Complete
- Tanggal: 2026-10-14 (UTC)
- Branch: `fase-3-kelas-jadwal`

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
- [x] Fase 3 — Kelas/Enrollment/Jadwal
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

### Fase 3 — Kelas/Enrollment/Jadwal (branch `fase-3-kelas-jadwal`)
- Migrasi: `2026_10_14_000004_create_class_enrollment_schedule_tables` (course_classes unique MK+semester+kode; class_lecturers + enrollments unique pivot; class_schedules hari+jam+ruang+rentang tanggal)
- 4 model: CourseClass (displayName, lecturers/students BelongsToMany), ClassLecturer, Enrollment, ClassSchedule (cast DayOfWeek, displayLabel) + enum DayOfWeek + relasi balik Semester/Course/Room + `isReferenced()` kini nyata
- Service: `ScheduleConflictService` (bentrok ruang/dosen/mahasiswa server-side + pesan jelas nama kelas/waktu/pihak terkait)
- Policy: `CourseClassPolicy` (admin kelola; dosen/mahasiswa view kelasnya) + 4 Form Requests
- 5 Controllers: CourseClass (scope role + filter), ClassLecturer (tolak duplikat), Enrollment (tolak duplikat + bentrok mhs), ClassSchedule (tolak bentrok, tidak disimpan), MySchedule (read-only + filter semester/hari)
- Routes: grup `role:superadmin,admin_academic,lecturer,student` + 9 views (kelas index/create/edit/show, dosen, enrollment, tambah enrollment, jadwal, jadwal-saya) + nav per role
- Tests: `ScheduleConflictTest` (6), `ClassManagementTest` (8) — 14 tests baru; fix `AcademicYearFactory` unique

### Fase 4 — Rekap Presensi Semester (lanjutan, branch `fase-3-kelas-jadwal`)
- Controller: `app/Http/Controllers/Meetings/SemesterAttendanceRecapController.php` — `index()` (daftar semester) + `show()` (rekap per kelas & per mahasiswa)
- Cakupan data: admin akademik/superadmin → seluruh kelas semester tersebut; dosen → hanya kelas yang ia ampu (`class_lecturers`); mahasiswa → 403 via Gate `view-semester-attendance-recap`
- Agregasi: hanya enrollment `status = aktif`; bucket `hadir/terlambat/izin/sakit/alpa/belum`; `hadir` = hadir + terlambat (`AttendanceStatus::countsAsPresent()`)
- Pertemuan tanpa catatan presensi dihitung sebagai `belum`
- Persentase kelas = jumlah record hadir ÷ (jumlah mahasiswa × jumlah pertemuan) — selalu 0–100%, konsisten dengan baris mahasiswa
- Persentase mahasiswa = jumlah record hadir ÷ jumlah pertemuan
- Optimasi: `scopedClasses()` dipakai bersama oleh `index()` (filter semester via `whereIn` + `distinct`) dan `show()`
- Routes: `attendance.recap.semesters` (GET `semesters/attendance-recap`), `attendance.recap.semester` (GET `semesters/{semester}/attendance-recap`) di grup `role:superadmin,admin_academic,lecturer`
- Views: `meetings/recap-semester-index`, `meetings/recap-semester` (kartu ringkasan, tabel per kelas, tabel per mahasiswa) + link nav "Rekap Semester" untuk admin & dosen
- Tests: `tests/Feature/SemesterAttendanceRecapTest.php` (8 tests) — 403 mahasiswa, akses admin/dosen, scoping kelas dosen, agregasi status, persentase kelas ≤100% dengan banyak mahasiswa, mahasiswa tanpa catatan = belum, persentase memakai potensi mahasiswa×pertemuan, enrollment nonaktif tidak dihitung


## Database changes
- Migration dibuat: `2026_10_14_000001_add_role_to_users_table` (kolom role default student + index)
- Dijalankan pada: sqlite `:memory:` via RefreshDatabase saat test (13 tests lolos termasuk skema role)
- Seeder: `DemoUsersSeeder` (4 akun demo, password `password`, dilewati di production)
- Rollback: `down()` drop index + kolom — aman, non-destruktif

### Fase 2
- Migration dibuat: `2026_10_14_000002_create_academic_master_tables`, `2026_10_14_000003_create_student_lecturer_profiles`
- Dijalankan pada: sqlite `:memory:` via RefreshDatabase saat test (28 tests lolos termasuk skema + FK + unique)
- Rollback: `down()` drop tabel urutan terbalik — aman, non-destruktif

### Fase 3
- Migration dibuat: `2026_10_14_000004_create_class_enrollment_schedule_tables`
- Dijalankan pada: sqlite `:memory:` via RefreshDatabase (42 tests lolos termasuk skema pivot + unique + FK)
- Rollback: `down()` drop urutan terbalik — aman, non-destruktif

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

### Fase 3
- Command: `php artisan test` (JUnit: `storage/logs/fase3-test3.xml`, di-ignore git)
- Hasil aktual: 42 tests, 179 assertions, 0 failures, 0 errors (28 lama + 14 baru)
- Run pertama error sintaks (insert tool memotong 2 file test) → diperbaiki manual; run kedua 40/42 (2 error flaky `AcademicYearFactory` duplikat kode tahun) → factory dibuat unique; run ketiga 100% lulus
- Cakupan baru: bentrok ruang/dosen/mahasiswa + pesan jelas, jadwal bersebelahan/beda-hari/beda-rentang tidak bentrok, enrollment & penugasan duplikat ditolak, jadwal bentrok tidak disimpan, validasi jam, scoping dosen/mahasiswa + 403 lintas kelas, filter semester/hari

### Fase 4 (rekap presensi semester)
- Command: `php artisan test --filter=SemesterAttendanceRecapTest` dan `php artisan test`
- Hasil aktual (terakhir): **77 tests, 265 assertions, 0 failures, 0 errors** — 42 lama (Fase 1–3) + 35 baru (Fase 4 attendance + rekap)
- Fokus rekap: 8 tests, 22 assertions, 0 failures
- Run pertama gagal karena helper test membuat `Meeting` baru untuk setiap catatan (duplikat `meeting_number` per kelas) → helper diubah ke `firstOrCreate` per nomor pertemuan
- Cakupan: otorisasi (mahasiswa 403, admin/dosen 200), scoping dosen hanya kelasnya, agregasi bucket status, persentase kelas ≤ 100% pada multi-mahasiswa, catatan kosong = `belum`, denominator = mahasiswa × pertemuan, enrollment nonaktif tidak masuk rekap

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

### Fase 3
- Jadwal konflik tidak disimpan tanpa keputusan eksplisit (sesuai PRD) — belum ada alur override; keputusan override admin ditunda ke Fase 9 (kuliah pengganti)
- Enrollment menolak bentrok jadwal mahasiswa otomatis (prasyarat "jika jadwal enrollment tersedia" terpenuhi — jadwal selalu tersedia di model ini)
- Kelas ber-enrollment/ber-jadwal tidak bisa dihapus (nonaktifkan via status); penugasan dosen bisa dilepas kapan saja
- Dosen/mahasiswa hanya read kelasnya (index di-scope + show 403); tombol kelola disembunyikan via `@can` + policy server-side

### Fase 4
- **Persentase kehadiran kelas memakai denominator `mahasiswa × pertemuan` (potensi), bukan jumlah catatan yang tercatat.** Alasannya: (1) tidak bisa melebihi 100%, (2) konsisten dengan baris per mahasiswa, (3) pertemuan tanpa catatan tetap dihitung sebagai `belum` di kolom rekap. Konsekuensinya, kelas dengan catatan presensi yang belum lengkap akan terlihat memiliki persentase lebih rendah — ini memang nilai "kehadiran aktual" yang berguna untuk monitoring.
- Presensi kosong (pertemuan tanpa catatan) masuk bucket `belum`, bukan `alpa` — `alpa` hanya dari status eksplisit `absent`
- Rekap hanya menghitung enrollment `aktif`; mahasiswa nonaktif/cuti tidak memengaruhi persentase kelas
- Dosen yang tidak punya kelas pada semester tersebut melihat daftar kelas kosong (bukan 403) — 403 hanya untuk role yang tidak berhak (mahasiswa)
- Rekap menampilkan data semester yang ada kelasnya saja (query di-scope ke kelas milik user), sehingga admin melihat daftar semester yang relevan

## Known issues / blockers
- Assets Vite belum di-build — halaman render tanpa CSS/JS sampai `npm run build`
- Shell integration bermasalah (exit code 1 meski sukses) — diverifikasi via output + JUnit + git log
- `.env` aktual tidak disentuh; reset email pakai mailer log/array (belum uji kirim asli)

## Next exact step
- Fase berikutnya: Fase 4 — Pertemuan, Presensi, Catatan Pengajaran
- Task terkecil: migrasi + model `meetings, attendance_sessions, attendances` + rekap + koreksi diaudit
- File PRD: `01_MASTER_SPEC.txt`, `06_FASE_04_PERTEMUAN_PRESENSI.txt`, `HANDOFF_STATE.md`
