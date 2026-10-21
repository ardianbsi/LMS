<?php

namespace App\Enums;

/**
 * Status kehadiran mahasiswa pada sesi presensi.
 *-values: present, late, excused, sick, absent.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Excused = 'excused';
    case Sick = 'sick';
    case Absent = 'absent';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Label Bahasa Indonesia untuk tampilan.
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Late => 'Terlambat',
            self::Excused => 'Izin',
            self::Sick => 'Sakit',
            self::Absent => 'Alpa',
        };
    }

    /**
     * Kunci bucket rekap (dipakai view & controller).
     */
    public function bucketKey(): string
    {
        return match ($this) {
            self::Present => 'hadir',
            self::Late => 'terlambat',
            self::Excused => 'izin',
            self::Sick => 'sakit',
            self::Absent => 'alpa',
        };
    }

    /**
     * Status yang tetap dihitung sebagai hadir pada rekap (hadir + terlambat).
     */
    public function countsAsPresent(): bool
    {
        return in_array($this, [self::Present, self::Late], true);
    }

    /**
     * Warna badge (Tailwind) sesuai status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'bg-emerald-100 text-emerald-700',
            self::Late => 'bg-amber-100 text-amber-700',
            self::Excused => 'bg-sky-100 text-sky-700',
            self::Sick => 'bg-violet-100 text-violet-700',
            self::Absent => 'bg-red-100 text-red-700',
        };
    }
}
