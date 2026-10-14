@extends('layouts.app')

@section('title', 'Detail Fakultas — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $faculty->name }}</h1>
        <a href="{{ route('master-data.faculties.edit', $faculty) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="mb-6 grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">Kode</dt><dd class="font-mono">{{ $faculty->code }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Deskripsi</dt><dd>{{ $faculty->description ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $faculty->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>

    <h2 class="mb-2 text-lg font-bold">Program Studi ({{ $faculty->studyPrograms->count() }})</h2>
    <ul class="max-w-xl space-y-2">
        @forelse ($faculty->studyPrograms as $program)
            <li class="rounded-lg bg-white px-4 py-2 text-sm shadow-sm">
                <span class="font-mono text-slate-500">{{ $program->code }}</span> — {{ $program->name }}
            </li>
        @empty
            <li class="text-sm text-slate-500">Belum ada program studi.</li>
        @endforelse
    </ul>
@endsection
