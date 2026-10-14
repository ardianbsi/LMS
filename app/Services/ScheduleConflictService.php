<?php

namespace App\Services;

use App\Enums\DayOfWeek;
use App\Models\ClassSchedule;
use App\Models\CourseClass;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

/**
 * Deteksi bentrok jadwal di sisi server (ruang, dosen, mahasiswa).
 * Setiap bentrok dijelaskan dengan nama kelas, waktu, dan dosen/ruang terkait.
 */
class ScheduleConflictService
{
    /**
     * @return array<int, array{type: string, message: string}>
     */
    public function checkForSchedule(ClassSchedule $schedule): array
    {
        $conflicts = [];

        foreach ($this->findRoomConflicts($schedule) as $other) {
            $conflicts[] = [
                'type' => 'ruang',
                'message' => $this->describeRoomConflict($schedule, $other),
            ];
        }

        foreach ($this->findLecturerConflicts($schedule) as $lecturerId => $others) {
            foreach ($others as $other) {
                $conflicts[] = [
                    'type' => 'dosen',
                    'message' => $this->describeLecturerConflict($schedule, $other, (int) $lecturerId),
                ];
            }
        }

        return $conflicts;
    }

    /**
     * @return array<int, array{type: string, message: string}>
     */
    public function checkStudentEnrollment(int $studentId, CourseClass $courseClass): array
    {
        $conflicts = [];

        $activeEnrollments = Enrollment::query()
            ->where('student_id', $studentId)
            ->where('status', 'aktif')
            ->where('course_class_id', '!=', $courseClass->id)
            ->with('courseClass.schedules.room')
            ->get();

        $newSchedules = $courseClass->schedules()->with('room')->get();

        foreach ($activeEnrollments as $enrollment) {
            $existingClass = $enrollment->courseClass;
            if (! $existingClass) {
                continue;
            }

            foreach ($existingClass->schedules as $existing) {
                foreach ($newSchedules as $new) {
                    if ($this->schedulesOverlap($existing, $new)) {
                        $conflicts[] = [
                            'type' => 'mahasiswa',
                            'message' => "Bentrok untuk mahasiswa: kelas {$courseClass->displayName()} ({$new->displayLabel()}) bertumpuk dengan {$existingClass->displayName()} ({$existing->displayLabel()}).",
                        ];
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Jadwal lain di ruang sama yang bertumpuk (di luar jadwal ini sendiri).
     *
     * @return \Illuminate\Support\Collection<int, ClassSchedule>
     */
    public function findRoomConflicts(ClassSchedule $schedule): Collection
    {
        if (! $schedule->room_id) {
            return collect();
        }

        return $this->overlappingSchedules($schedule)
            ->where('room_id', $schedule->room_id)
            ->values();
    }

    /**
     * Dikelompokkan per dosen: [lecturer_id => Collection jadwal lain].
     *
     * @return array<int, \Illuminate\Support\Collection<int, ClassSchedule>>
     */
    public function findLecturerConflicts(ClassSchedule $schedule): array
    {
        $lecturerIds = $schedule->courseClass
            ? $schedule->courseClass->classLecturers()->pluck('lecturer_id')->all()
            : [];

        if ($lecturerIds === []) {
            return [];
        }

        $result = [];

        foreach ($this->overlappingSchedules($schedule) as $other) {
            $otherLecturerIds = $other->courseClass
                ? $other->courseClass->classLecturers()->pluck('lecturer_id')->all()
                : [];

            foreach (array_intersect($lecturerIds, $otherLecturerIds) as $lecturerId) {
                $result[(int) $lecturerId] ??= collect();
                $result[(int) $lecturerId]->push($other);
            }
        }

        return $result;
    }

    /**
     * Kandidat jadwal lain: hari sama, jam bertumpuk, rentang tanggal bertumpuk.
     *
     * @return \Illuminate\Support\Collection<int, ClassSchedule>
     */
    private function overlappingSchedules(ClassSchedule $schedule): Collection
    {
        $day = $schedule->day_of_week instanceof DayOfWeek
            ? $schedule->day_of_week->value
            : (string) $schedule->day_of_week;

        $candidates = ClassSchedule::query()
            ->with(['courseClass.classLecturers.lecturer.user', 'room'])
            ->where('id', '!=', $schedule->id ?? 0)
            ->where('day_of_week', $day)
            ->where('course_class_id', '!=', $schedule->course_class_id)
            ->where('start_time', '<', $schedule->end_time)
            ->where('end_time', '>', $schedule->start_time)
            ->get();

        return $candidates->filter(fn (ClassSchedule $other) => $this->dateRangesOverlap($schedule, $other))->values();
    }

    /**
     * Dua jadwal bertumpuk bila hari sama + jam bertumpuk + rentang tanggal bertumpuk.
     */
    public function schedulesOverlap(ClassSchedule $a, ClassSchedule $b): bool
    {
        $dayA = $a->day_of_week instanceof DayOfWeek ? $a->day_of_week->value : (string) $a->day_of_week;
        $dayB = $b->day_of_week instanceof DayOfWeek ? $b->day_of_week->value : (string) $b->day_of_week;

        if ($dayA !== $dayB) {
            return false;
        }

        if (! ($a->start_time < $b->end_time && $a->end_time > $b->start_time)) {
            return false;
        }

        return $this->dateRangesOverlap($a, $b);
    }

    /**
     * Rentang tanggal bertumpuk; null = tak terbatas di sisi tersebut.
     */
    private function dateRangesOverlap(ClassSchedule $a, ClassSchedule $b): bool
    {
        $startA = $a->valid_from?->toDateString() ?? '0000-00-00';
        $endA = $a->valid_until?->toDateString() ?? '9999-12-31';
        $startB = $b->valid_from?->toDateString() ?? '0000-00-00';
        $endB = $b->valid_until?->toDateString() ?? '9999-12-31';

        return $startA <= $endB && $startB <= $endA;
    }

    private function describeRoomConflict(ClassSchedule $schedule, ClassSchedule $other): string
    {
        $room = $other->room?->code ?? $schedule->room?->code ?? '—';
        $otherClass = $other->courseClass?->displayName() ?? 'kelas lain';

        return "Bentrok ruang {$room}: jadwal {$schedule->displayLabel()} bertumpuk dengan {$otherClass} ({$other->displayLabel()}).";
    }

    private function describeLecturerConflict(ClassSchedule $schedule, ClassSchedule $other, int $lecturerId): string
    {
        $lecturer = $other->courseClass?->classLecturers->firstWhere('lecturer_id', $lecturerId)?->lecturer;
        $name = $lecturer?->displayName() ?: "dosen ID {$lecturerId}";
        $otherClass = $other->courseClass?->displayName() ?? 'kelas lain';

        return "Bentrok dosen {$name}: jadwal {$schedule->displayLabel()} bertumpuk dengan {$otherClass} ({$other->displayLabel()}).";
    }
}
