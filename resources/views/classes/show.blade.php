@extends('layouts.app')

@section('title', 'Detail Kelas — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ $courseClass->displayName() }}</h1>
            <p class="text-sm text-slate-500">{{ $courseClass->semester?->academicYear?->name }} — {{ $courseClass->semester?->name }} · Status: {{ ucfirst($courseClass->status) }}</p>
        </div>
        @can('update', $courseClass)
            <a href="{{ route('classes.edit', $courseClass) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah Kelas</a>
        @endcan
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

    @can('update', $courseClass)
        <div class="mb-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('classes.lecturers.index', $courseClass) }}" class="rounded-lg bg-white px-3 py-2 shadow-sm hover:bg-slate-200">Kelola Dosen ({{ $courseClass->classLecturers->count() }})</a>
            <a href="{{ route('classes.enrollments.index', $courseClass) }}" class="rounded-lg bg-white px-3 py-2 shadow-sm hover:bg-slate-200">Kelola Mahasiswa ({{ $courseClass->enrollments->count() }})</a>
            <a href="{{ route('classes.schedules.index', $courseClass) }}" class="rounded-lg bg-white px-3 py-2 shadow-sm hover:bg-slate-200">Kelola Jadwal ({{ $courseClass->schedules->count() }})</a>
        </div>
    @endcan

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-2 font-bold">Dosen Pengampu</h2>
            <ul class="space-y-1 text-sm">
                @forelse ($courseClass->classLecturers as $assignment)
                    <li>{{ $assignment->lecturer?->displayName() }} <span class="text-slate-400">({{ $assignment->role }})</span></li>
                @empty
                    <li class="text-slate-500">Belum ada dosen.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-2 font-bold">Jadwal ({{ $courseClass->schedules->count() }})</h2>
            <ul class="space-y-1 text-sm">
                @forelse ($courseClass->schedules as $schedule)
                    <li>{{ $schedule->displayLabel() }}</li>
                @empty
                    <li class="text-slate-500">Belum ada jadwal.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h2 class="mb-2 font-bold">Mahasiswa ({{ $courseClass->enrollments->count() }})</h2>
            <ul class="space-y-1 text-sm">
                @forelse ($courseClass->enrollments->take(10) as $enrollment)
                    <li><span class="font-mono text-slate-500">{{ $enrollment->student?->nim }}</span> {{ $enrollment->student?->displayName() }}</li>
                @empty
                    <li class="text-slate-500">Belum ada mahasiswa.</li>
                @endforelse
                @if ($courseClass->enrollments->count() > 10)
                    <li class="text-slate-400">… dan {{ $courseClass->enrollments->count() - 10 }} lainnya.</li>
                @endif
            </ul>
        </div>
    </div>
@endsection
