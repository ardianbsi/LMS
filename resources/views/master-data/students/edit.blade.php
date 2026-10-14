@extends('layouts.app')

@section('title', 'Ubah Mahasiswa — Master Data')

@section('content')
    @include('master-data._nav')

    <h1 class="mb-4 text-2xl font-bold">Ubah Mahasiswa: {{ $student->user?->name }}</h1>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('master-data.students.update', $student) }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $student->user?->name) }}" maxlength="255"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="email" class="mb-1 block text-sm font-semibold">Email Akun</label>
            <input id="email" name="email" type="email" value="{{ old('email', $student->user?->email) }}" maxlength="255"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="nim" class="mb-1 block text-sm font-semibold">NIM *</label>
                <input id="nim" name="nim" type="text" value="{{ old('nim', $student->nim) }}" required maxlength="32"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="entry_year" class="mb-1 block text-sm font-semibold">Angkatan *</label>
                <input id="entry_year" name="entry_year" type="number" min="2000" max="2100" value="{{ old('entry_year', $student->entry_year) }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="study_program_id" class="mb-1 block text-sm font-semibold">Program Studi</label>
            <select id="study_program_id" name="study_program_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">—</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected(old('study_program_id', $student->study_program_id) == $program->id)>{{ $program->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="phone" class="mb-1 block text-sm font-semibold">Telepon</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone', $student->phone) }}" maxlength="32"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="address" class="mb-1 block text-sm font-semibold">Alamat</label>
            <textarea id="address" name="address" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('address', $student->address) }}</textarea>
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-semibold">Status</label>
            <select id="status" name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach (['aktif', 'cuti', 'lulus', 'nonaktif'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $student->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('master-data.students.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
