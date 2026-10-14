@extends('layouts.app')

@section('title', 'Tambah Ruang — Master Data')

@section('content')
    @include('master-data._nav')

    <h1 class="mb-4 text-2xl font-bold">Tambah Ruang</h1>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('master-data.rooms.store') }}" class="max-w-xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
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
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="building" class="mb-1 block text-sm font-semibold">Gedung</label>
                <input id="building" name="building" type="text" value="{{ old('building') }}" maxlength="128"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="capacity" class="mb-1 block text-sm font-semibold">Kapasitas</label>
                <input id="capacity" name="capacity" type="number" min="1" max="1000" value="{{ old('capacity') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
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
            <a href="{{ route('master-data.rooms.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
