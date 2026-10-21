<?php

namespace App\Http\Controllers\Meetings;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Semester;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

/**
 * Rekap presensi per semester (Fase 4).
 *
 * Cakupan data:
 * - Admin akademik: seluruh kelas pada semester tersebut.
 * - Dosen: hanya kelas yang ia ampu pada semester tersebut.
 */
class SemesterAttendanceRecapController extends Controller
{
    use AuthorizesRequests;

    /**
     * Daftar semester yang memiliki rekap presensi.
     */
    public function index(): View
    {
        $this->authorize('view-semester-attendance-recap');

        $semesterIds = $this->scopedClasses()
            ->distinct()
            ->pluck('semester_id');

        $semesters = Semester::query()
            ->with('academicYear')
            ->whereIn('id', $semesterIds)
            ->orderByDesc('start_date')
            ->get();

        return view('meetings.recap-semester-index', compact('semesters'));
    }

    /**
     * Rekap presensi satu semester: agregasi per kelas dan per mahasiswa.
     */
    public function show(Semester $semester): View
    {
        $this->authorize('view-semester-attendance-recap');

        $classes = $this->visibleClasses($semester)
            ->with(['course', 'meetings.attendanceSession.attendances', 'enrollments.student.user'])
            ->orderBy('class_code')
            ->get();

        $classRows = [];
        $studentRows = [];
        $totals = $this->emptyTotals();

        foreach ($classes as $courseClass) {
            $classStudentRows = $this->buildStudentRows($courseClass);

            $studentRows = array_merge($studentRows, $classStudentRows);
            $classRows[] = $this->buildClassRow($courseClass, $classStudentRows, $totals);
        }

        $studentRows = array_values($studentRows);
        usort($studentRows, fn (array $a, array $b) => strcasecmp($a['nama'], $b['nama']));

        return view('meetings.recap-semester', compact('semester', 'classRows', 'studentRows', 'totals'));
    }

    /**
     * Kelas yang boleh dilihat user pada semester ini.
     */
    private function visibleClasses(Semester $semester)
    {
        return $this->scopedClasses()->where('semester_id', $semester->id);
    }

    /**
     * Seluruh kelas yang boleh dilihat user (dosen: hanya kelas yang ia ampu).
     */
    private function scopedClasses()
    {
        $query = CourseClass::query();

        $user = request()->user();

        if (! $user->isAdminAcademic() && ! $user->isSuperadmin()) {
            $lecturerId = $user->lecturer?->id;

            abort_unless($lecturerId, 403);

            $query->whereHas('classLecturers', fn ($q) => $q->where('lecturer_id', $lecturerId));
        }

        return $query;
    }

    /**
     * Bucket status rekap (hadir + terlambat = dianggap hadir).
     *
     * @return array<int, string>
     */
    private function buckets(): array
    {
        return ['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'belum'];
    }

    /**
     * Bucket status kosong.
     *
     * @return array<string, int>
     */
    private function emptyTotals(): array
    {
        return array_fill_keys($this->buckets(), 0);
    }

    /**
     * Agregasi satu kelas: total pertemuan, status, dan persentase kehadiran.
     *
     * Persentase dihitung dari baris mahasiswa kelas tersebut: jumlah-record
     * "hadir" (hadir + terlambat) dibagi jumlah-mahasiswa x jumlah-pertemuan.
     * Karena satu mahasiswa hanya punya satu catatan per pertemuan, nilai
     * persentase selalu berada pada rentang 0-100%.
     *
     * @param  array<int, array<string, mixed>>  $studentRows
     * @param  array<string, int>  $totals
     * @return array<string, mixed>
     */
    private function buildClassRow(CourseClass $courseClass, array $studentRows, array &$totals): array
    {
        $counts = $this->emptyTotals();
        $hadir = 0;
        $meetings = $courseClass->meetings->count();

        foreach ($studentRows as $row) {
            foreach ($this->buckets() as $key) {
                $counts[$key] += $row['counts'][$key];
                $totals[$key] += $row['counts'][$key];
            }

            $hadir += $row['hadir'];
        }

        // Potensi: mahasiswa enrolled x pertemuan kelas.
        $potential = count($studentRows) * $meetings;

        return [
            'kelas' => $courseClass,
            'nama' => $courseClass->displayName(),
            'pertemuan' => $meetings,
            'mahasiswa' => count($studentRows),
            'counts' => $counts,
            'hadir' => $hadir,
            'persentase' => $potential > 0 ? round($hadir / $potential * 100, 1) : 0.0,
        ];
    }

    /**
     * Agregasi per mahasiswa pada satu kelas (hanya mahasiswa enrolled aktif).
     *
     * Pertemuan tanpa catatan presensi dihitung sebagai "belum".
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildStudentRows(CourseClass $courseClass): array
    {
        $enrolled = $courseClass->enrollments
            ->filter(fn ($enrollment) => $enrollment->status === 'aktif')
            ->map(fn ($enrollment) => $enrollment->student)
            ->filter()
            ->keyBy('id');

        $meetings = $courseClass->meetings->count();
        $rows = [];

        foreach ($enrolled as $student) {
            $counts = $this->emptyTotals();
            $hadir = 0;
            $recorded = 0;

            foreach ($courseClass->meetings as $meeting) {
                $attendance = $meeting->attendanceSession?->attendances
                    ->firstWhere('student_id', $student->id);

                $status = $attendance?->status instanceof AttendanceStatus
                    ? $attendance->status
                    : ($attendance ? AttendanceStatus::tryFrom((string) $attendance->status) : null);

                if ($status === null) {
                    $counts['belum']++;

                    continue;
                }

                $recorded++;
                $counts[$status->bucketKey()]++;

                if ($status->countsAsPresent()) {
                    $hadir++;
                }
            }

            $rows[] = [
                'kelas' => $courseClass->displayName(),
                'student' => $student,
                'nama' => $student->displayName(),
                'nim' => $student->nim,
                'pertemuan' => $meetings,
                'recorded' => $recorded,
                'counts' => $counts,
                'hadir' => $hadir,
                'persentase' => $meetings > 0 ? round($hadir / $meetings * 100, 1) : 0.0,
            ];
        }

        return $rows;
    }
}
