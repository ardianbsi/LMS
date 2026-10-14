@extends('layouts.app')

@section('title', 'Detail Mahasiswa — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $student->user?->name }}</h1>
        <a href="{{ route('master-data.students.edit', $student) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">NIM</dt><dd class="font-mono">{{ $student->nim }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Email</dt><dd>{{ $student->user?->email ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Program Studi</dt><dd>{{ $student->studyProgram?->name ?? '—' }} ({{ $student->studyProgram?->faculty?->name ?? '—' }})</dd></div>
        <div><dt class="font-semibold text-slate-500">Angkatan</dt><dd>{{ $student->entry_year }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Telepon</dt><dd>{{ $student->phone ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Alamat</dt><dd>{{ $student->address ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ ucfirst($student->status) }}</dd></div>
    </dl>
@endsection
