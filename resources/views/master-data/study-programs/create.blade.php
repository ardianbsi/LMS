@extends('layouts.app')

@section('title', 'Tambah Program Studi — Master Data')

@section('content')
    @include('master-data._nav')

    <h1 class="mb-4 text-2xl font-bold">Tambah Program Studi</h1>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('master-data.study-programs.store') }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label for="faculty_id" class="mb-1 block text-sm font-semibold">Fakultas *</label>
            <select id="faculty_id" name="faculty_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">— Pilih fakultas —</option>
                @foreach ($faculties as $faculty)
                    <option value="{{ $faculty->id }}" @selected(old('faculty_id') == $faculty->id)>{{ $faculty->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="code" class="mb-1 block text-sm font-semibold">Kode *</label>
            <input id="code" name="code" type="text" value="{{ old('code') }}" required maxlength="32"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama *</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="255"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="degree" class="mb-1 block text-sm font-semibold">Jenjang</label>
            <select id="degree" name="degree" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach (['D3', 'S1', 'S2', 'S3'] as $degree)
                    <option value="{{ $degree }}" @selected(old('degree') === $degree)>{{ $degree }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="description" class="mb-1 block text-sm font-semibold">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description') }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300">
            Aktif
        </label>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('master-data.study-programs.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
