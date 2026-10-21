@extends('layouts.app')

@section('title', 'Rekap Presensi Semester — MyCampus LMS')

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-bold">Rekap Presensi Semester</h1>
        <p class="text-sm text-slate-500">Pilih semester untuk melihat rekap presensi kelas & mahasiswa.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">Semester</th>
                    <th class="px-4 py-3">Tahun Akademik</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($semesters as $semester)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $semester->name }}</td>
                        <td class="px-4 py-3">{{ $semester->academicYear?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            {{ $semester->start_date?->format('d/m/Y') ?? '—' }} — {{ $semester->end_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($semester->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('attendance.recap.semester', $semester) }}" class="font-semibold text-indigo-600 hover:underline">Lihat Rekap</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada semester dengan data presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
