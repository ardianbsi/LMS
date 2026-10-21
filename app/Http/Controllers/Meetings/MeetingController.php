<?php

namespace App\Http\Controllers\Meetings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Meetings\MeetingRequest;
use App\Models\CourseClass;
use App\Models\Meeting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MeetingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Daftar pertemuan dalam kelas (dosen pengampu, admin, mahasiswa enrolled).
     */
    public function index(CourseClass $courseClass): View
    {
        $this->authorize('view', $courseClass);

        $courseClass->load(['meetings.attendanceSession']);

        return view('meetings.index', compact('courseClass'));
    }

    public function create(CourseClass $courseClass): View
    {
        $this->authorize('manageMeetings', $courseClass);

        return view('meetings.create', compact('courseClass'));
    }

    /**
     * Simpan pertemuan baru; nomor pertemuan unik per kelas.
     */
    public function store(MeetingRequest $request, CourseClass $courseClass): RedirectResponse
    {
        $this->authorize('manageMeetings', $courseClass);

        $courseClass->meetings()->create($request->validated());

        return redirect()
            ->route('meetings.index', $courseClass)
            ->with('status', 'Pertemuan berhasil dibuat.');
    }

    public function show(CourseClass $courseClass, Meeting $meeting): View
    {
        $this->authorize('view', $meeting);

        abort_unless((int) $meeting->course_class_id === (int) $courseClass->id, 404);

        $meeting->load([
            'attendanceSession.attendances.student.user',
            'attendanceSession.attendances.corrections.actor',
        ]);

        return view('meetings.show', compact('courseClass', 'meeting'));
    }

    public function edit(CourseClass $courseClass, Meeting $meeting): View
    {
        $this->authorize('update', $meeting);

        abort_unless((int) $meeting->course_class_id === (int) $courseClass->id, 404);

        return view('meetings.edit', compact('courseClass', 'meeting'));
    }

    /**
     * Ubah pertemuan; nomor tetap unik per kelas.
     */
    public function update(MeetingRequest $request, CourseClass $courseClass, Meeting $meeting): RedirectResponse
    {
        $this->authorize('update', $meeting);

        abort_unless((int) $meeting->course_class_id === (int) $courseClass->id, 404);

        $meeting->update($request->validated());

        return redirect()
            ->route('meetings.index', $courseClass)
            ->with('status', 'Pertemuan berhasil diperbarui.');
    }

    /**
     * Hapus pertemuan; ditolak bila sudah ada data presensi.
     */
    public function destroy(CourseClass $courseClass, Meeting $meeting): RedirectResponse
    {
        $this->authorize('delete', $meeting);

        abort_unless((int) $meeting->course_class_id === (int) $courseClass->id, 404);

        if ($meeting->attendanceSession?->attendances()->exists()) {
            return back()->withErrors(['meeting' => 'Pertemuan tidak dapat dihapus karena sudah memiliki data presensi.']);
        }

        $meeting->delete();

        return redirect()
            ->route('meetings.index', $courseClass)
            ->with('status', 'Pertemuan dihapus.');
    }
}
