@extends('layouts.app')

@section('title', 'Semester — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Semester</h1>
            <p class="text-sm text-slate-500">Setiap semester terikat pada satu tahun akademik.</p>
        </div>
        <a href="{{ route('master-data.semesters.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Tambah Semester</a>
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

    <form method="GET" action="{{ route('master-data.semesters.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode/nama..."
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <select name="academic_year_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua tahun akademik</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">Kode Term</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Tahun Akademik</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($semesters as $semester)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-mono">{{ $semester->term_code }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('master-data.semesters.show', $semester) }}" class="font-semibold text-indigo-600 hover:underline">{{ $semester->name }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $semester->academicYear?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $semester->start_date->format('d M Y') }} – {{ $semester->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $semester->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('master-data.semesters.edit', $semester) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 hover:bg-slate-200">Ubah</a>
                                <form method="POST" action="{{ route('master-data.semesters.toggle', $semester) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg bg-amber-100 px-3 py-1.5 text-amber-800 hover:bg-amber-200">
                                        {{ $semester->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('master-data.semesters.destroy', $semester) }}" onsubmit="return confirm('Hapus semester ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada data semester.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $semesters->links() }}</div>
@endsection
