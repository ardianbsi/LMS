@extends('layouts.app')

@section('title', 'Jadwal Kelas — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Jadwal Kelas</h1>
    <p class="mb-4 text-sm text-slate-500">{{ $courseClass->displayName() }} — jadwal bentrok (ruang/dosen) otomatis ditolak sistem.</p>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('update', $courseClass)
        <form method="POST" action="{{ route('classes.schedules.store', $courseClass) }}" class="mb-4 grid max-w-3xl gap-2 rounded-xl bg-white p-4 shadow-sm sm:grid-cols-6">
            @csrf
            <select name="day_of_week" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Hari</option>
                @foreach (['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('day_of_week') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="time" name="start_time" value="{{ old('start_time') }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input type="time" name="end_time" value="{{ old('end_time') }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select name="room_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tanpa ruang</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>{{ $room->code }} — {{ $room->name }}</option>
                @endforeach
            </select>
            <input type="text" name="note" value="{{ old('note') }}" placeholder="Catatan" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Tambah</button>
        </form>
    @endcan

    <ul class="max-w-3xl space-y-2">
        @forelse ($courseClass->schedules as $schedule)
            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-4 py-2 text-sm shadow-sm">
                <span class="font-semibold">{{ $schedule->displayLabel() }}</span>
                @can('update', $courseClass)
                    <form method="POST" action="{{ route('classes.schedules.destroy', [$courseClass, $schedule]) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">Hapus</button>
                    </form>
                @endcan
            </li>
        @empty
            <li class="text-sm text-slate-500">Belum ada jadwal.</li>
        @endforelse
    </ul>

    <p class="mt-4"><a href="{{ route('classes.show', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke detail kelas</a></p>
@endsection
