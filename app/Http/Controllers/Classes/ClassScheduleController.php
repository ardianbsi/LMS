<?php

namespace App\Http\Controllers\Classes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classes\ClassScheduleRequest;
use App\Models\ClassSchedule;
use App\Models\CourseClass;
use App\Models\Room;
use App\Services\ScheduleConflictService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(CourseClass $courseClass): View
    {
        $this->authorize('view', $courseClass);

        $courseClass->load('schedules.room');
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        return view('classes.schedules', compact('courseClass', 'rooms'));
    }

    /**
     * Simpan jadwal; bentrok ruang/dosen ditolak — tidak disimpan tanpa keputusan eksplisit.
     */
    public function store(ClassScheduleRequest $request, CourseClass $courseClass, ScheduleConflictService $conflicts): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        $schedule = new ClassSchedule($request->validated() + ['course_class_id' => $courseClass->id]);
        $schedule->courseClass()->associate($courseClass);

        $found = $conflicts->checkForSchedule($schedule);

        if ($found !== []) {
            return back()
                ->withInput()
                ->withErrors(['schedule' => $found[0]['message']]);
        }

        $courseClass->schedules()->create($request->validated());

        return back()->with('status', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Ubah jadwal; cek bentrok ulang dengan mengecualikan jadwal ini sendiri.
     */
    public function update(ClassScheduleRequest $request, CourseClass $courseClass, ClassSchedule $schedule, ScheduleConflictService $conflicts): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        if ($schedule->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $schedule->fill($request->validated());
        $schedule->courseClass()->associate($courseClass);

        $found = $conflicts->checkForSchedule($schedule);

        if ($found !== []) {
            return back()
                ->withInput()
                ->withErrors(['schedule' => $found[0]['message']]);
        }

        $schedule->save();

        return back()->with('status', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(CourseClass $courseClass, ClassSchedule $schedule): RedirectResponse
    {
        $this->authorize('update', $courseClass);

        if ($schedule->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $schedule->delete();

        return back()->with('status', 'Jadwal dihapus.');
    }
}
