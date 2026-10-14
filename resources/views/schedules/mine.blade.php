@extends('layouts.app')

@section('title', 'Jadwal Saya — MyCampus LMS')

@section('content')
    <h1 class="mb-4 text-2xl font-bold">Jadwal Saya</h1>

    <form method="GET" action="{{ route('schedules.mine') }}" class="mb-4 flex flex-wrap gap-2">
        <select name="semester_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua semester</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) request('semester_id') === (string) $semester->id)>{{ $semester->name }} ({{ $semester->term_code }})</option>
            @endforeach
        </select>
        <select name="day" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua hari</option>
            @foreach ($days as $day)
                <option value="{{ $day->value }}" @selected(request('day') === $day->value)>{{ $day->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">Hari</th>
                    <th class="px-4 py-3">Jam</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Ruang</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $schedule->day_of_week instanceof \App\Enums\DayOfWeek ? $schedule->day_of_week->label() : $schedule->day_of_week }}</td>
                        <td class="px-4 py-3 font-mono">{{ substr((string) $schedule->start_time, 0, 5) }}–{{ substr((string) $schedule->end_time, 0, 5) }}</td>
                        <td class="px-4 py-3">{{ $schedule->courseClass?->displayName() }}</td>
                        <td class="px-4 py-3">{{ $schedule->room?->code ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada jadwal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $schedules->links() }}</div>
@endsection
