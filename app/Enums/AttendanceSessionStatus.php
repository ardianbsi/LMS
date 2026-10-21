<?php

namespace App\Enums;

/**
 * Status sesi presensi pada sebuah pertemuan.
 */
enum AttendanceSessionStatus: string
{
    case NotStarted = 'belum';
    case Open = 'buka';
    case Closed = 'tutup';

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
            self::NotStarted => 'Belum dimulai',
            self::Open => 'Terbuka',
            self::Closed => 'Ditutup',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::NotStarted => 'bg-amber-100 text-amber-700',
            self::Open => 'bg-emerald-100 text-emerald-700',
            self::Closed => 'bg-slate-200 text-slate-600',
        };
    }
}
