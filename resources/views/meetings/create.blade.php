@extends('layouts.app')

@section('title', 'Buat Pertemuan — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Buat Pertemuan</h1>
    <p class="mb-4 text-sm text-slate-500">{{ $courseClass->displayName() }} — nomor pertemuan unik per kelas.</p>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('meetings.store', $courseClass) }}" class="max-w-2xl space-y-4 rounded-xl bg-white p-6 shadow-sm">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="meeting_number" class="mb-1 block text-sm font-semibold">Nomor Pertemuan *</label>
                <input id="meeting_number" name="meeting_number" type="number" min="1" max="999" value="{{ old('meeting_number') }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="title" class="mb-1 block text-sm font-semibold">Judul</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" maxlength="255"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <div>
                <label for="meeting_date" class="mb-1 block text-sm font-semibold">Tanggal</label>
                <input id="meeting_date" name="meeting_date" type="date" value="{{ old('meeting_date') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="start_time" class="mb-1 block text-sm font-semibold">Mulai</label>
                <input id="start_time" name="start_time" type="time" value="{{ old('start_time') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="end_time" class="mb-1 block text-sm font-semibold">Selesai</label>
                <input id="end_time" name="end_time" type="time" value="{{ old('end_time') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="topic" class="mb-1 block text-sm font-semibold">Pokok Bahasan</label>
            <textarea id="topic" name="topic" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('topic') }}</textarea>
        </div>
        <div>
            <label for="summary" class="mb-1 block text-sm font-semibold">Ringkasan</label>
            <textarea id="summary" name="summary" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('summary') }}</textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('meetings.index', $courseClass) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm hover:bg-slate-200">Batal</a>
        </div>
    </form>
@endsection
