<?php

namespace App\Http\Controllers\Meetings;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

/**
 * Rekap presensi (Fase 4): per kelas dan per mahasiswa.
 */
class AttendanceRecapController extends Controller
{
    use AuthorizesRequests;

    /**
     * Rekap presensi per pertemuan pada satu kelas (admin & dosen pengampu).
     */
    public function classSummary(CourseClass $courseClass): View
    {
        $this->authorize('viewAttendanceRecap', $courseClass);

        $meetings = $courseClass->meetings()
            ->with(['attendanceSession.attendances'])
            ->orderBy('meeting_number')
            ->get();

        $totals = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
        ];

        foreach ($meetings as $meeting) {
            foreach ($meeting->attendanceSession?->attendances ?? [] as $attendance) {
                $status = $attendance->status instanceof AttendanceStatus
                    ? $attendance->status
                    : AttendanceStatus::tryFrom((string) $attendance->status);

                if ($status === null) {
                    continue;
                }

                $totals[$status->bucketKey()]++;
            }
        }

        return view('meetings.recap-class', compact('courseClass', 'meetings', 'totals'));
    }

    /**
     * Rekap presensi seorang mahasiswa pada kelas yang diikuti.
     */
    public function studentSummary(CourseClass $courseClass, Student $student): View
    {
        $isSelf = request()->user()->student?->id === $student->id;

        abort_unless(
            $isSelf || request()->user()->can('viewAttendanceRecap', $courseClass),
            403
        );

        $enrolled = $courseClass->enrollments()
            ->where('student_id', $student->id)
            ->where('status', 'aktif')
            ->exists();

        abort_unless($enrolled, 404);

        $rows = $courseClass->meetings()
            ->with(['attendanceSession.attendances' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderBy('meeting_number')
            ->get();

        $counts = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'belum' => 0,
        ];

        $hadir = 0;

        foreach ($rows as $meeting) {
            $attendance = $meeting->attendanceSession?->attendances->first();
            $status = $attendance?->status instanceof AttendanceStatus
                ? $attendance->status
                : ($attendance ? AttendanceStatus::tryFrom((string) $attendance->status) : null);

            if ($status === null) {
                $counts['belum']++;

                continue;
            }

            $counts[$status->bucketKey()]++;

            // Hadir + terlambat sama-sama dihitung sebagai hadir.
            if ($status->countsAsPresent()) {
                $hadir++;
            }
        }

        $total = $rows->count();
        $percentage = $total > 0 ? round($hadir / $total * 100, 1) : 0.0;

        return view('meetings.recap-student', compact('courseClass', 'student', 'rows', 'counts', 'hadir', 'percentage', 'total'));
    }
}
