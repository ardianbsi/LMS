@extends('layouts.app')

@section('title', 'Rekap Presensi ' . $semester->name . ' — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Rekap Presensi Semester</h1>
            <p class="text-sm text-slate-500">
                {{ $semester->name }}
                @if ($semester->academicYear) · {{ $semester->academicYear->name }} @endif
            </p>
        </div>
        <a href="{{ route('attendance.recap.semesters') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100">Daftar Semester</a>
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

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-6">
        @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa', 'belum' => 'Belum'] as $key => $label)
            <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                <p class="text-2xl font-bold">{{ $totals[$key] }}</p>
                <p class="text-xs text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mb-2 text-lg font-semibold">Rekap per Kelas</h2>
    <p class="mb-2 text-xs text-slate-500">
        Persentase kehadiran = jumlah status hadir (hadir + terlambat) dibagi jumlah potensi (mahasiswa × pertemuan).
    </p>
    <div class="mb-6 overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Pertemuan</th>
                    <th class="px-4 py-3">Mahasiswa</th>
                    <th class="px-4 py-3">Hadir</th>
                    <th class="px-4 py-3">Terlambat</th>
                    <th class="px-4 py-3">Izin</th>
                    <th class="px-4 py-3">Sakit</th>
                    <th class="px-4 py-3">Alpa</th>
                    <th class="px-4 py-3">Belum</th>
                    <th class="px-4 py-3">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classRows as $row)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $row['nama'] }}</td>
                        <td class="px-4 py-3">{{ $row['pertemuan'] }}</td>
                        <td class="px-4 py-3">{{ $row['mahasiswa'] }}</td>
                        @foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'belum'] as $key)
                            <td class="px-4 py-3">{{ $row['counts'][$key] }}</td>
                        @endforeach
                        <td class="px-4 py-3 font-semibold">{{ $row['persentase'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada kelas pada semester ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mb-2 text-lg font-semibold">Rekap per Mahasiswa</h2>
    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">NIM</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Hadir</th>
                    <th class="px-4 py-3">Terlambat</th>
                    <th class="px-4 py-3">Izin</th>
                    <th class="px-4 py-3">Sakit</th>
                    <th class="px-4 py-3">Alpa</th>
                    <th class="px-4 py-3">Belum</th>
                    <th class="px-4 py-3">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($studentRows as $row)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-semibold">{{ $row['nim'] }}</td>
                        <td class="px-4 py-3">{{ $row['nama'] }}</td>
                        <td class="px-4 py-3">{{ $row['kelas'] }}</td>
                        @foreach (['hadir', 'terlambat', 'izin', 'sakit', 'alpa', 'belum'] as $key)
                            <td class="px-4 py-3">{{ $row['counts'][$key] }}</td>
                        @endforeach
                        <td class="px-4 py-3 font-semibold">{{ $row['persentase'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-slate-500">Belum ada data mahasiswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
