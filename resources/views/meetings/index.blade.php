@extends('layouts.app')

@section('title', 'Pertemuan Kelas — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Pertemuan</h1>
            <p class="text-sm text-slate-500">{{ $courseClass->displayName() }} — {{ $courseClass->meetings->count() }} pertemuan</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('viewAttendanceRecap', $courseClass)
                <a href="{{ route('attendance.recap.class', $courseClass) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100">Rekap Presensi</a>
            @endcan
            @can('manageMeetings', $courseClass)
                <a href="{{ route('meetings.create', $courseClass) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Pertemuan</a>
            @endcan
        </div>
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

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Sesi</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courseClass->meetings as $meeting)
                    @php $session = $meeting->attendanceSession; @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $meeting->meeting_number }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('meetings.show', [$courseClass, $meeting]) }}" class="font-semibold text-indigo-600 hover:underline">{{ $meeting->title ?: 'Tanpa judul' }}</a>
                            @if ($meeting->topic)
                                <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($meeting->topic, 70) }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $meeting->meeting_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($session)
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $session->status->badgeClass() }}">{{ $session->status->label() }}</span>
                            @else
                                <span class="text-xs text-slate-400">Belum ada sesi</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('meetings.session.show', [$courseClass, $meeting]) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold hover:bg-slate-200">Presensi</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada pertemuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-4"><a href="{{ route('classes.show', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke detail kelas</a></p>
@endsection
