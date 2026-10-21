<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit koreksi status presensi: actor, waktu, alasan, status lama/baru.
 */
class AttendanceCorrection extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'attendance_id',
        'old_status',
        'new_status',
        'reason',
        'actor_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_status' => AttendanceStatus::class,
            'new_status' => AttendanceStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
