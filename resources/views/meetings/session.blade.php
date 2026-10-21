@extends('layouts.app')

@section('title', 'Sesi Presensi — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Presensi — {{ $meeting->displayName() }}</h1>
            <p class="text-sm text-slate-500">{{ $courseClass->displayName() }} · {{ $meeting->meeting_date?->format('d/m/Y') ?? 'Tanpa tanggal' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $session->status->badgeClass() }}">{{ $session->status->label() }}</span>
            @can('manageSession', $meeting)
                @if ($session->isOpen())
                    <form method="POST" action="{{ route('meetings.session.close', [$courseClass, $meeting]) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Tutup Sesi</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('meetings.session.open', [$courseClass, $meeting]) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Buka Sesi</button>
                    </form>
                @endif
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

    @if (auth()->user()->isStudent() && $session->isOpen())
        <form method="POST" action="{{ route('meetings.session.self', [$courseClass, $meeting]) }}" class="mb-4 flex flex-wrap items-end gap-2 rounded-xl bg-white p-4 shadow-sm">
            @csrf
            <div class="flex-1">
                <label for="self_note" class="mb-1 block text-sm font-semibold">Catatan (opsional)</label>
                <input id="self_note" name="note" type="text" value="{{ old('note') }}" maxlength="500"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Presensi Sekarang</button>
        </form>
    @endif

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">NIM</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Catatan</th>
                    @can('mark', $meeting)
                        <th class="px-4 py-3 text-right">Catat / Koreksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($enrolled as $enrollment)
                    @php
                        $student = $enrollment->student;
                        $attendance = $attendances->get($student->id);
                        $status = $attendance?->status;
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-mono">{{ $student?->nim }}</td>
                        <td class="px-4 py-3">{{ $student?->displayName() }}</td>
                        <td class="px-4 py-3">
                            @if ($status)
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $status->badgeClass() }}">{{ $status->label() }}</span>
                            @else
                                <span class="text-xs text-slate-400">Belum absen</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $attendance?->note ?? '—' }}</td>
                        @can('mark', $meeting)
                            <td class="px-4 py-3 text-right">
                                @include('meetings.partials.attendance-actions', ['courseClass' => $courseClass, 'meeting' => $meeting, 'attendance' => $attendance, 'student' => $student, 'status' => $status])
                            </td>
                        @endcan
                    </tr>
                    @if ($attendance && $attendance->corrections->isNotEmpty())
                        <tr class="border-b border-slate-100 bg-slate-50 last:border-0">
                            <td colspan="6" class="px-4 py-2 text-xs text-slate-500">
                                <span class="font-semibold">Riwayat koreksi:</span>
                                @foreach ($attendance->corrections as $correction)
                                    <span class="ml-2">{{ $correction->old_status->label() }} → {{ $correction->new_status->label() }} ({{ $correction->actor?->name ?? 'Tanpa actor' }}, {{ $correction->created_at?->format('d/m/Y H:i') }}) — {{ $correction->reason }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada mahasiswa terdaftar aktif pada kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-4"><a href="{{ route('meetings.index', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke daftar pertemuan</a></p>
@endsection
