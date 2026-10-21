<?php

namespace App\Services;

use App\Enums\AttendanceSessionStatus;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Alur presensi (Fase 4).
 *
 * Aturan:
 * - Presensi hanya jika sesi masih terbuka.
 * - Satu attendance per mahasiswa per sesi (idempotent).
 * - Koreksi status mencatat actor, waktu, dan alasan.
 */
class AttendanceService
{
    /**
     * Catat kehadiran mahasiswa pada sesi.
     *
     * @throws RuntimeException bila sesi belum dibuka atau sudah ditutup.
     */
    public function record(
        AttendanceSession $session,
        Student $student,
        AttendanceStatus $status = AttendanceStatus::Present,
        ?string $method = 'self',
        ?string $note = null
    ): Attendance {
        if ($session->status === AttendanceSessionStatus::NotStarted) {
            throw new RuntimeException('Sesi presensi belum dibuka.');
        }

        if (! $session->isOpen()) {
            throw new RuntimeException('Sesi presensi sudah ditutup.');
        }

        return DB::transaction(function () use ($session, $student, $status, $method, $note) {
            $existing = Attendance::where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            return Attendance::create([
                'attendance_session_id' => $session->id,
                'student_id' => $student->id,
                'status' => $status->value,
                'checked_in_at' => now(),
                'method' => $method,
                'note' => $note,
            ]);
        });
    }

    /**
     * Koreksi status presensi dengan audit actor/waktu/alasan.
     *
     * @throws RuntimeException bila status tidak berubah.
     */
    public function correct(Attendance $attendance, AttendanceStatus $newStatus, string $reason, User $actor): AttendanceCorrection
    {
        $oldStatus = $attendance->status;

        if (! $oldStatus instanceof AttendanceStatus) {
            $oldStatus = AttendanceStatus::tryFrom((string) $oldStatus) ?? AttendanceStatus::Absent;
        }

        if ($oldStatus === $newStatus) {
            throw new RuntimeException('Status presensi sudah sama, tidak ada perubahan yang dicatat.');
        }

        return DB::transaction(function () use ($attendance, $oldStatus, $newStatus, $reason, $actor) {
            $attendance->update(['status' => $newStatus->value]);

            return AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
                'reason' => $reason,
                'actor_id' => $actor->id,
            ]);
        });
    }

    /**
     * Buka sesi presensi untuk sebuah pertemuan (idempotent).
     */
    public function openSession(AttendanceSession $session, User $actor): AttendanceSession
    {
        if ($session->status === AttendanceSessionStatus::Open) {
            return $session;
        }

        $session->update([
            'status' => AttendanceSessionStatus::Open->value,
            'opened_at' => now(),
            'closed_at' => null,
            'opened_by' => $actor->id,
            'closed_by' => null,
        ]);

        return $session;
    }

    /**
     * Tutup sesi presensi (idempotent).
     *
     * @throws RuntimeException bila sesi belum pernah dibuka.
     */
    public function closeSession(AttendanceSession $session, User $actor): AttendanceSession
    {
        if ($session->status === AttendanceSessionStatus::Closed) {
            return $session;
        }

        if ($session->status === AttendanceSessionStatus::NotStarted) {
            throw new RuntimeException('Sesi presensi belum dibuka.');
        }

        $session->update([
            'status' => AttendanceSessionStatus::Closed->value,
            'closed_at' => now(),
            'closed_by' => $actor->id,
        ]);

        return $session;
    }
}
