@extends('layouts.app')

@section('title', 'Kelas — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">{{ auth()->user()->hasRole('lecturer') || auth()->user()->hasRole('student') ? 'Kelas Saya' : 'Kelas' }}</h1>
            <p class="text-sm text-slate-500">Mata kuliah pada semester dan kode kelas tertentu.</p>
        </div>
        @can('create', App\Models\CourseClass::class)
            <a href="{{ route('classes.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Buat Kelas</a>
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

    <form method="GET" action="{{ route('classes.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode MK/nama/kelas..."
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <select name="semester_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua semester</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) request('semester_id') === (string) $semester->id)>{{ $semester->academicYear?->name }} — {{ $semester->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach (['aktif', 'nonaktif', 'selesai'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Semester</th>
                    <th class="px-4 py-3">Jadwal</th>
                    <th class="px-4 py-3">Mhs</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3">
                            <a href="{{ route('classes.show', $class) }}" class="font-semibold text-indigo-600 hover:underline">{{ $class->displayName() }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $class->semester?->name }} ({{ $class->semester?->term_code }})</td>
                        <td class="px-4 py-3">{{ $class->schedules_count }} sesi</td>
                        <td class="px-4 py-3">{{ $class->enrollments_count }} mhs</td>
                        <td class="px-4 py-3">{{ ucfirst($class->status) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @can('update', $class)
                                    <a href="{{ route('classes.edit', $class) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 hover:bg-slate-200">Ubah</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $classes->links() }}</div>
@endsection
