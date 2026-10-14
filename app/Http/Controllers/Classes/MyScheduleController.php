<?php

namespace App\Http\Controllers\Classes;

use App\Enums\DayOfWeek;
use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyScheduleController extends Controller
{
    /**
     * Jadwal read-only untuk dosen & mahasiswa yang login.
     * Filter: semester + hari. Dosen: kelas ditugaskan. Mahasiswa: kelas diikuti.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $schedules = ClassSchedule::query()
            ->with(['courseClass.course', 'courseClass.semester', 'room'])
            ->when($user->hasRole('lecturer') && $user->lecturer, function ($query) use ($user) {
                $query->whereHas('courseClass.classLecturers', fn ($q) => $q->where('lecturer_id', $user->lecturer->id));
            })
            ->when($user->hasRole('student') && $user->student, function ($query) use ($user) {
                $query->whereHas(
                    'courseClass.enrollments',
                    fn ($q) => $q->where('student_id', $user->student->id)->where('status', 'aktif')
                );
            })
            ->when($request->integer('semester_id'), function ($query, $id) {
                $query->whereHas('courseClass', fn ($q) => $q->where('semester_id', $id));
            })
            ->when($request->string('day')->toString(), fn ($query, $day) => $query->where('day_of_week', $day))
            ->orderByRaw($this->dayOrderSql())
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();
        $days = DayOfWeek::cases();

        return view('schedules.mine', compact('schedules', 'semesters', 'days'));
    }

    /**
     * Urutan hari Senin..Minggu untuk ORDER BY lintas DB.
     */
    private function dayOrderSql(): string
    {
        return "CASE day_of_week
            WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3
            WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6
            WHEN 'minggu' THEN 7 ELSE 8 END";
    }
}
