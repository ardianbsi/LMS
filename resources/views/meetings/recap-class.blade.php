@extends('layouts.app')

@section('title', 'Rekap Presensi Kelas — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Rekap Presensi</h1>
            <p class="text-sm text-slate-500">{{ $courseClass->displayName() }} — rekap per pertemuan.</p>
        </div>
        <a href="{{ route('meetings.index', $courseClass) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100">Daftar Pertemuan</a>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $key => $label)
            <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                <p class="text-2xl font-bold">{{ $totals[$key] }}</p>
                <p class="text-xs text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Sesi</th>
                    <th class="px-4 py-3">Hadir</th>
                    <th class="px-4 py-3">Terlambat</th>
                    <th class="px-4 py-3">Izin</th>
                    <th class="px-4 py-3">Sakit</th>
                    <th class="px-4 py-3">Alpa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($meetings as $meeting)
                    @php
                        $counts = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
                        foreach ($meeting->attendanceSession?->attendances ?? [] as $item) {
                            $itemStatus = $item->status instanceof \App\Enums\AttendanceStatus
                                ? $item->status
                                : \App\Enums\AttendanceStatus::tryFrom((string) $item->status);
                            if (! $itemStatus) { continue; }
                            $counts[$itemStatus->bucketKey()]++;
                        }
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $meeting->meeting_number }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('meetings.show', [$courseClass, $meeting]) }}" class="font-semibold text-indigo-600 hover:underline">{{ $meeting->title ?: 'Tanpa judul' }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $meeting->meeting_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($meeting->attendanceSession)
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $meeting->attendanceSession->status->badgeClass() }}">{{ $meeting->attendanceSession->status->label() }}</span>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        @foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpa'] as $key)
                            <td class="px-4 py-3">{{ $counts[$key] }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada pertemuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-4"><a href="{{ route('classes.show', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke detail kelas</a></p>
@endsection
