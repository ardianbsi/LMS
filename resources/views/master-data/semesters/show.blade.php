@extends('layouts.app')

@section('title', 'Detail Semester — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $semester->name }} ({{ $semester->term_code }})</h1>
        <a href="{{ route('master-data.semesters.edit', $semester) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">Tahun Akademik</dt><dd>{{ $semester->academicYear?->name ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Periode</dt><dd>{{ $semester->start_date->format('d M Y') }} – {{ $semester->end_date->format('d M Y') }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>
@endsection
