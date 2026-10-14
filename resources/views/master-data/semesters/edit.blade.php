@extends('layouts.app')

@section('title', 'Ubah Semester — Master Data')

@section('content')
    @include('master-data._nav')

    <h1 class="mb-4 text-2xl font-bold">Ubah Semester: {{ $semester->term_code }}</h1>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('master-data.semesters.update', $semester) }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label for="academic_year_id" class="mb-1 block text-sm font-semibold">Tahun Akademik *</label>
            <select id="academic_year_id" name="academic_year_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($years as $year)
                    <option value="{{ $year->id }}" @selected(old('academic_year_id', $semester->academic_year_id) == $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="term_code" class="mb-1 block text-sm font-semibold">Kode Term *</label>
            <input id="term_code" name="term_code" type="text" value="{{ old('term_code', $semester->term_code) }}" required maxlength="16"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama *</label>
            <input id="name" name="name" type="text" value="{{ old('name', $semester->name) }}" required maxlength="64"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="start_date" class="mb-1 block text-sm font-semibold">Tanggal Mulai *</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $semester->start_date->format('Y-m-d')) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="end_date" class="mb-1 block text-sm font-semibold">Tanggal Selesai *</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $semester->end_date->format('Y-m-d')) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $semester->is_active)) class="rounded border-slate-300">
            Aktif
        </label>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('master-data.semesters.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
