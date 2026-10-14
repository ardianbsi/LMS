@extends('layouts.app')

@section('title', 'Dosen Pengampu — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Dosen Pengampu</h1>
    <p class="mb-4 text-sm text-slate-500">{{ $courseClass->displayName() }}</p>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('classes.lecturers.store', $courseClass) }}" class="mb-4 flex max-w-xl flex-wrap gap-2 rounded-xl bg-white p-4 shadow-sm">
        @csrf
        <select name="lecturer_id" required class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">— Pilih dosen —</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}">{{ $lecturer->displayName() }} ({{ $lecturer->nip }})</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Tugaskan</button>
    </form>

    <ul class="max-w-xl space-y-2">
        @forelse ($courseClass->classLecturers as $assignment)
            <li class="flex items-center justify-between gap-3 rounded-lg bg-white px-4 py-2 text-sm shadow-sm">
                <span>{{ $assignment->lecturer?->displayName() }} <span class="text-slate-400">({{ $assignment->lecturer?->nip }}, {{ $assignment->role }})</span></span>
                <form method="POST" action="{{ route('classes.lecturers.destroy', [$courseClass, $assignment]) }}" onsubmit="return confirm('Hapus penugasan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">Hapus</button>
                </form>
            </li>
        @empty
            <li class="text-sm text-slate-500">Belum ada dosen ditugaskan.</li>
        @endforelse
    </ul>

    <p class="mt-4"><a href="{{ route('classes.show', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke detail kelas</a></p>
@endsection
