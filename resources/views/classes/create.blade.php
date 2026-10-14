@extends('layouts.app')

@section('title', 'Buat Kelas — MyCampus LMS')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Buat Kelas</h1>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('classes.store') }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label for="course_id" class="mb-1 block text-sm font-semibold">Mata Kuliah *</label>
            <select id="course_id" name="course_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">— Pilih MK —</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->code }} — {{ $course->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="semester_id" class="mb-1 block text-sm font-semibold">Semester *</label>
            <select id="semester_id" name="semester_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">— Pilih semester —</option>
                @foreach ($semesters as $semester)
                    <option value="{{ $semester->id }}" @selected(old('semester_id') == $semester->id)>{{ $semester->academicYear?->name }} — {{ $semester->name }} ({{ $semester->term_code }})</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="class_code" class="mb-1 block text-sm font-semibold">Kode Kelas * (cth: A)</label>
                <input id="class_code" name="class_code" type="text" value="{{ old('class_code') }}" required maxlength="16"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="capacity" class="mb-1 block text-sm font-semibold">Kapasitas</label>
                <input id="capacity" name="capacity" type="number" min="1" max="500" value="{{ old('capacity') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama Tambahan (opsional)</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('classes.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
