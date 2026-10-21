<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Classes\ClassLecturerController;
use App\Http\Controllers\Classes\ClassScheduleController;
use App\Http\Controllers\Classes\CourseClassController;
use App\Http\Controllers\Classes\EnrollmentController;
use App\Http\Controllers\Classes\MyScheduleController;
use App\Http\Controllers\MasterData\AcademicYearController;
use App\Http\Controllers\MasterData\CourseController;
use App\Http\Controllers\MasterData\FacultyController;
use App\Http\Controllers\MasterData\LecturerController;
use App\Http\Controllers\MasterData\RoomController;
use App\Http\Controllers\MasterData\SemesterController;
use App\Http\Controllers\MasterData\StudentController;
use App\Http\Controllers\MasterData\StudyProgramController;
use App\Http\Controllers\Meetings\AttendanceRecapController;
use App\Http\Controllers\Meetings\AttendanceSessionController;
use App\Http\Controllers\Meetings\MeetingController;
use App\Http\Controllers\Meetings\SemesterAttendanceRecapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest: login & lupa kata sandi.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/lupa-kata-sandi', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/lupa-kata-sandi', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/reset-kata-sandi/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-kata-sandi', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

// Authenticated.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard/superadmin', [DashboardController::class, 'superadmin'])
        ->middleware('role:superadmin')
        ->name('dashboard.superadmin');

    Route::get('/dashboard/admin-akademik', [DashboardController::class, 'adminAcademic'])
        ->middleware('role:superadmin,admin_academic')
        ->name('dashboard.admin-academic');

    Route::get('/dashboard/dosen', [DashboardController::class, 'lecturer'])
        ->middleware('role:superadmin,lecturer')
        ->name('dashboard.lecturer');

    Route::get('/dashboard/mahasiswa', [DashboardController::class, 'student'])
        ->middleware('role:superadmin,student')
        ->name('dashboard.student');

    // Master data akademik: admin-only (superadmin via Gate::before + admin_academic).
    Route::prefix('master-data')->name('master-data.')->middleware('role:superadmin,admin_academic')->group(function () {
        Route::resource('faculties', FacultyController::class)->parameters(['faculties' => 'faculty']);
        Route::patch('faculties/{faculty}/toggle', [FacultyController::class, 'toggle'])->name('faculties.toggle');

        Route::resource('study-programs', StudyProgramController::class)->parameters(['study-programs' => 'studyProgram']);
        Route::patch('study-programs/{studyProgram}/toggle', [StudyProgramController::class, 'toggle'])->name('study-programs.toggle');

        Route::resource('academic-years', AcademicYearController::class)->parameters(['academic-years' => 'academicYear']);
        Route::patch('academic-years/{academicYear}/toggle', [AcademicYearController::class, 'toggle'])->name('academic-years.toggle');

        Route::resource('semesters', SemesterController::class);
        Route::patch('semesters/{semester}/toggle', [SemesterController::class, 'toggle'])->name('semesters.toggle');

        Route::resource('courses', CourseController::class)->parameters(['courses' => 'course']);
        Route::patch('courses/{course}/toggle', [CourseController::class, 'toggle'])->name('courses.toggle');

        Route::resource('rooms', RoomController::class);
        Route::patch('rooms/{room}/toggle', [RoomController::class, 'toggle'])->name('rooms.toggle');

        Route::resource('students', StudentController::class);
        Route::resource('lecturers', LecturerController::class);
    });

    // Kelas & jadwal.
    // - Admin (superadmin/admin_academic): kelola penuh.
    // - Dosen/mahasiswa: baca kelasnya + jadwalnya (di-scope di controller + policy).
    Route::middleware('role:superadmin,admin_academic,lecturer,student')->group(function () {
        Route::resource('classes', CourseClassController::class)->parameters(['classes' => 'courseClass']);

        Route::get('classes/{courseClass}/lecturers', [ClassLecturerController::class, 'index'])->name('classes.lecturers.index');
        Route::post('classes/{courseClass}/lecturers', [ClassLecturerController::class, 'store'])->name('classes.lecturers.store');
        Route::delete('classes/{courseClass}/lecturers/{lecturer}', [ClassLecturerController::class, 'destroy'])->name('classes.lecturers.destroy');

        Route::get('classes/{courseClass}/enrollments', [EnrollmentController::class, 'index'])->name('classes.enrollments.index');
        Route::get('classes/{courseClass}/enrollments/add', [EnrollmentController::class, 'candidates'])->name('classes.enrollments.candidates');
        Route::post('classes/{courseClass}/enrollments', [EnrollmentController::class, 'store'])->name('classes.enrollments.store');
        Route::delete('classes/{courseClass}/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('classes.enrollments.destroy');

        Route::get('classes/{courseClass}/schedules', [ClassScheduleController::class, 'index'])->name('classes.schedules.index');
        Route::post('classes/{courseClass}/schedules', [ClassScheduleController::class, 'store'])->name('classes.schedules.store');
        Route::put('classes/{courseClass}/schedules/{schedule}', [ClassScheduleController::class, 'update'])->name('classes.schedules.update');
        Route::delete('classes/{courseClass}/schedules/{schedule}', [ClassScheduleController::class, 'destroy'])->name('classes.schedules.destroy');

        Route::get('jadwal-saya', [MyScheduleController::class, 'index'])->name('schedules.mine');
    });

    // Pertemuan & presensi (Fase 4).
    // - Admin: kelola penuh + koreksi.
    // - Dosen: kelola pertemuan & sesi presensi kelas yang ditugaskan.
    // - Mahasiswa: lihat pertemuan kelasnya & presensi diri saat sesi terbuka.
    Route::middleware('role:superadmin,admin_academic,lecturer,student')->group(function () {
        Route::get('classes/{courseClass}/meetings', [MeetingController::class, 'index'])->name('meetings.index');
        Route::get('classes/{courseClass}/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
        Route::post('classes/{courseClass}/meetings', [MeetingController::class, 'store'])->name('meetings.store');
        Route::get('classes/{courseClass}/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
        Route::get('classes/{courseClass}/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
        Route::put('classes/{courseClass}/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::delete('classes/{courseClass}/meetings/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

        Route::get('classes/{courseClass}/meetings/{meeting}/session', [AttendanceSessionController::class, 'show'])->name('meetings.session.show');
        Route::post('classes/{courseClass}/meetings/{meeting}/session/open', [AttendanceSessionController::class, 'open'])->name('meetings.session.open');
        Route::post('classes/{courseClass}/meetings/{meeting}/session/close', [AttendanceSessionController::class, 'close'])->name('meetings.session.close');
        Route::post('classes/{courseClass}/meetings/{meeting}/session/attendances', [AttendanceSessionController::class, 'store'])->name('meetings.session.attendances.store');
        Route::post('classes/{courseClass}/meetings/{meeting}/session/self', [AttendanceSessionController::class, 'selfCheck'])->name('meetings.session.self');
        Route::put('classes/{courseClass}/meetings/{meeting}/session/attendances/{attendance}', [AttendanceSessionController::class, 'correct'])->name('meetings.session.attendances.correct');

        Route::get('classes/{courseClass}/attendance-recap', [AttendanceRecapController::class, 'classSummary'])->name('attendance.recap.class');
        Route::get('classes/{courseClass}/attendance-recap/{student}', [AttendanceRecapController::class, 'studentSummary'])->name('attendance.recap.student');

        // Rekap presensi per semester (Fase 4).
        Route::get('semesters/attendance-recap', [SemesterAttendanceRecapController::class, 'index'])->name('attendance.recap.semesters');
        Route::get('semesters/{semester}/attendance-recap', [SemesterAttendanceRecapController::class, 'show'])->name('attendance.recap.semester');
    });
});

