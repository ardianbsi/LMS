@extends('layouts.app')

@section('title', 'Ubah Mata Kuliah — Master Data')

@section('content')
    @include('master-data._nav')

    <h1 class="mb-4 text-2xl font-bold">Ubah Mata Kuliah: {{ $course->name }}</h1>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('master-data.courses.update', $course) }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label for="study_program_id" class="mb-1 block text-sm font-semibold">Program Studi *</label>
            <select id="study_program_id" name="study_program_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected(old('study_program_id', $course->study_program_id) == $program->id)>{{ $program->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="code" class="mb-1 block text-sm font-semibold">Kode *</label>
            <input id="code" name="code" type="text" value="{{ old('code', $course->code) }}" required maxlength="32"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama *</label>
            <input id="name" name="name" type="text" value="{{ old('name', $course->name) }}" required maxlength="255"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="credits" class="mb-1 block text-sm font-semibold">SKS *</label>
                <input id="credits" name="credits" type="number" min="1" max="12" value="{{ old('credits', $course->credits) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="semester_number" class="mb-1 block text-sm font-semibold">Semester Ke</label>
                <input id="semester_number" name="semester_number" type="number" min="1" max="14" value="{{ old('semester_number', $course->semester_number) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="description" class="mb-1 block text-sm font-semibold">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $course->description) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $course->is_active)) class="rounded border-slate-300">
            Aktif
        </label>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('master-data.courses.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
