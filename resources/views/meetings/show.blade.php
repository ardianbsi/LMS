@extends('layouts.app')

@section('title', 'Detail Pertemuan — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $meeting->displayName() }}</h1>
            <p class="text-sm text-slate-500">{{ $courseClass->displayName() }} · {{ $meeting->meeting_date?->format('d/m/Y') ?? 'Tanpa tanggal' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('meetings.session.show', [$courseClass, $meeting]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Kelola Presensi</a>
            @can('update', $meeting)
                <a href="{{ route('meetings.edit', [$courseClass, $meeting]) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100">Ubah</a>
            @endcan
            @can('delete', $meeting)
                <form method="POST" action="{{ route('meetings.destroy', [$courseClass, $meeting]) }}" onsubmit="return confirm('Hapus pertemuan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-100 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-200">Hapus</button>
                </form>
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

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-2 font-bold">Pokok Bahasan</h2>
            <p class="whitespace-pre-line text-sm text-slate-700">{{ $meeting->topic ?: '—' }}</p>

            <h2 class="mb-2 mt-4 font-bold">Ringkasan</h2>
            <p class="whitespace-pre-line text-sm text-slate-700">{{ $meeting->summary ?: '—' }}</p>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-2 font-bold">Sesi Presensi</h2>
            @if ($session = $meeting->attendanceSession)
                <p><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $session->status->badgeClass() }}">{{ $session->status->label() }}</span></p>
                <p class="mt-2 text-xs text-slate-500">Dibuka: {{ $session->opened_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="text-xs text-slate-500">Ditutup: {{ $session->closed_at?->format('d/m/Y H:i') ?? '—' }}</p>
                <p class="mt-2 text-sm">Tercatat: {{ $session->attendances->count() }} mahasiswa</p>
            @else
                <p class="text-sm text-slate-500">Belum ada sesi presensi.</p>
            @endif
        </div>
    </div>

    <p class="mt-4"><a href="{{ route('meetings.index', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke daftar pertemuan</a></p>
@endsection
