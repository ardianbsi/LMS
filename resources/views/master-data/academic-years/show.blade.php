@extends('layouts.app')

@section('title', 'Detail Tahun Akademik — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $academicYear->name }}</h1>
        <a href="{{ route('master-data.academic-years.edit', $academicYear) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="mb-6 grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">Kode</dt><dd class="font-mono">{{ $academicYear->code }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Periode</dt><dd>{{ $academicYear->start_date->format('d M Y') }} – {{ $academicYear->end_date->format('d M Y') }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>

    <h2 class="mb-2 text-lg font-bold">Semester ({{ $academicYear->semesters->count() }})</h2>
    <ul class="max-w-xl space-y-2">
        @forelse ($academicYear->semesters as $semester)
            <li class="rounded-lg bg-white px-4 py-2 text-sm shadow-sm">
                <span class="font-mono text-slate-500">{{ $semester->term_code }}</span> — {{ $semester->name }}
            </li>
        @empty
            <li class="text-sm text-slate-500">Belum ada semester.</li>
        @endforelse
    </ul>
@endsection
