@extends('layouts.app')

@section('title', 'Detail Ruang — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ $room->name }}</h1>
        <a href="{{ route('master-data.rooms.edit', $room) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Ubah</a>
    </div>

    <dl class="grid max-w-xl gap-3 rounded-xl bg-white p-6 text-sm shadow-sm">
        <div><dt class="font-semibold text-slate-500">Kode</dt><dd class="font-mono">{{ $room->code }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Gedung</dt><dd>{{ $room->building ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Kapasitas</dt><dd>{{ $room->capacity ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Deskripsi</dt><dd>{{ $room->description ?? '—' }}</dd></div>
        <div><dt class="font-semibold text-slate-500">Status</dt><dd>{{ $room->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
    </dl>
@endsection
