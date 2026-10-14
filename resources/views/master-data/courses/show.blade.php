@extends('layouts.app')

@section('title', 'Detail Mata Kuliah — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $course->name }}</h1>
        <a href="{{ route('master-data.courses.edit', $course) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">Kode</dt><dd class="font-mono">{{ $course->code }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Program Studi</dt><dd>{{ $course->studyProgram?->name ?? '—' }} ({{ $course->studyProgram?->faculty?->name ?? '—' }})</dd></div>
        <div><dt class="font-semibold text-slate-500">SKS</dt><dd>{{ $course->credits }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Semester Ke</dt><dd>{{ $course->semester_number ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Deskripsi</dt><dd>{{ $course->description ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $course->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>
@endsection
