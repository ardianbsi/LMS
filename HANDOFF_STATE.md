# HANDOFF STATE — MyCampus LMS

> AI coding agent: perbarui dokumen ini di akhir setiap fase. Jangan mengklaim pekerjaan selesai tanpa verifikasi.

## Current status
- Fase terakhir: Fase 1 — Fondasi, Autentikasi, Role/Permission, Layout
- Status: Complete
- Tanggal: 2026-10-14 (UTC)
- Branch: `fase-1-fondasi` (commit handoff menyusul)

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
- [ ] Fase 2 — Master data
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


## Database changes
- Migration dibuat: `2026_10_14_000001_add_role_to_users_table` (kolom role default student + index)
- Dijalankan pada: sqlite `:memory:` via RefreshDatabase saat test (13 tests lolos termasuk skema role)
- Seeder: `DemoUsersSeeder` (4 akun demo, password `password`, dilewati di production)
- Rollback: `down()` drop index + kolom — aman, non-destruktif

## Tests
- Command: `php artisan test` (JUnit: `storage/logs/fase1-test2.xml`, dibersihkan setelah verifikasi)
- Hasil aktual: 13 tests, 52 assertions, 0 failures, 0 errors
- Run pertama 3 gagal (`ViteManifestNotFoundException`); diperbaiki via `withoutVite()` di TestCase
- `npm run build` tidak dijalankan (node/npm belum terverifikasi) — known issue visual saja

## Decisions
- Paket A: SQLite dev + role native + Blade manual, tanpa package baru (hindari risiko Laravel 13)
- `laravel/boost --dev` belum diinstall (butuh izin eksplisit install dependency)
- Dashboard orisinal (Matrix Admin tidak tersedia di repo)
- Rate limit login 5/menit/IP; reset 5/menit; superadmin Gate::before allow-all

## Known issues / blockers
- Assets Vite belum di-build — halaman render tanpa CSS/JS sampai `npm run build`
- Shell integration bermasalah (exit code 1 meski sukses) — diverifikasi via output + JUnit + git log
- `.env` aktual tidak disentuh; reset email pakai mailer log/array (belum uji kirim asli)

## Next exact step
- Fase berikutnya: Fase 2 — Master Data Akademik
- Task terkecil: migrasi + model + CRUD Fakultas (search/filter/pagination, nonaktif vs hapus, policy admin-only)
- File PRD: `01_MASTER_SPEC.txt`, `04_FASE_02_MASTER_DATA.txt`, `HANDOFF_STATE.md`
