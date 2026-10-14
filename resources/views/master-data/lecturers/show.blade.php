@extends('layouts.app')

@section('title', 'Detail Dosen — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $lecturer->user?->name }}</h1>
        <a href="{{ route('master-data.lecturers.edit', $lecturer) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">NIP</dt><dd class="font-mono">{{ $lecturer->nip }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Email</dt><dd>{{ $lecturer->user?->email ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Gelar</dt><dd>{{ $lecturer->academic_title ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Program Studi</dt><dd>{{ $lecturer->studyProgram?->name ?? '—' }} ({{ $lecturer->studyProgram?->faculty?->name ?? '—' }})</dd></div>
        <div><dt class="font-semibold text-slate-500">Telepon</dt><dd>{{ $lecturer->phone ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ ucfirst($lecturer->status) }}</dd></div>
    </dl>
@endsection
