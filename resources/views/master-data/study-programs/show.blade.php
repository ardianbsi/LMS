@extends('layouts.app')

@section('title', 'Detail Program Studi — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $studyProgram->name }}</h1>
        <a href="{{ route('master-data.study-programs.edit', $studyProgram) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="mb-6 grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">Kode</dt><dd class="font-mono">{{ $studyProgram->code }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Fakultas</dt><dd>{{ $studyProgram->faculty?->name ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Jenjang</dt><dd>{{ $studyProgram->degree ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Deskripsi</dt><dd>{{ $studyProgram->description ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $studyProgram->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>

    <h2 class="mb-2 text-lg font-bold">Mata Kuliah ({{ $studyProgram->courses->count() }})</h2>
    <ul class="max-w-xl space-y-2">
        @forelse ($studyProgram->courses as $course)
            <li class="rounded-lg bg-white px-4 py-2 text-sm shadow-sm">
                <span class="font-mono text-slate-500">{{ $course->code }}</span> — {{ $course->name }}
            </li>
        @empty
            <li class="text-sm text-slate-500">Belum ada mata kuliah.</li>
        @endforelse
    </ul>
@endsection
