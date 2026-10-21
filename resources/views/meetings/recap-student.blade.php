@extends('layouts.app')

@section('title', 'Rekap Presensi Mahasiswa — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Rekap Presensi — {{ $student->displayName() }}</h1>
            <p class="text-sm text-slate-500">{{ $student->nim }} · {{ $courseClass->displayName() }}</p>
        </div>
        @can('viewAttendanceRecap', $courseClass)
            <a href="{{ route('attendance.recap.class', $courseClass) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100">Rekap Kelas</a>
        @endcan
    </div>

    <div class="mb-4 grid grid-cols-3 gap-3 sm:grid-cols-6">
        @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa', 'belum' => 'Belum Absen'] as $key => $label)
            <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                <p class="text-2xl font-bold">{{ $counts[$key] }}</p>
                <p class="text-xs text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-4 rounded-xl bg-white p-4 shadow-sm">
        <p class="text-sm font-semibold">Persentase kehadiran: <span class="text-indigo-700">{{ $percentage }}%</span> ({{ $hadir }}/{{ $total }} pertemuan)</p>
        <div class="mt-2 h-2 w-full rounded-full bg-slate-200">
            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ min(100, $percentage) }}%"></div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $meeting)
                    @php
                        $item = $meeting->attendanceSession?->attendances->first();
                        $itemStatus = $item?->status instanceof \App\Enums\AttendanceStatus
                            ? $item->status
                            : ($item ? \App\Enums\AttendanceStatus::tryFrom((string) $item->status) : null);
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $meeting->meeting_number }}</td>
                        <td class="px-4 py-3">{{ $meeting->title ?: 'Tanpa judul' }}</td>
                        <td class="px-4 py-3">{{ $meeting->meeting_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($itemStatus)
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $itemStatus->badgeClass() }}">{{ $itemStatus->label() }}</span>
                            @else
                                <span class="text-xs text-slate-400">Belum absen</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada pertemuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-4"><a href="{{ route('meetings.index', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke daftar pertemuan</a></p>
@endsection
