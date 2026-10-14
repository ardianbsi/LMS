@extends('layouts.app')

@section('title', 'Dosen — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Dosen</h1>
            <p class="text-sm text-slate-500">NIP unik. Nama/email diambil dari akun user.</p>
        </div>
        <a href="{{ route('master-data.lecturers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Tambah Dosen</a>
    </div>

    <form method="GET" action="{{ route('master-data.lecturers.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIP/nama/email..."
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <select name="study_program_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua prodi</option>
            @foreach ($programs as $program)
                <option value="{{ $program->id }}" @selected((string) request('study_program_id') === (string) $program->id)>{{ $program->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach (['aktif', 'nonaktif'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="px-4 py-3">NIP</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Prodi</th>
                    <th class="px-4 py-3">Gelar</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lecturers as $lecturer)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-mono">{{ $lecturer->nip }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('master-data.lecturers.show', $lecturer) }}" class="font-semibold text-indigo-600 hover:underline">{{ $lecturer->user?->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $lecturer->user?->email ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $lecturer->studyProgram?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $lecturer->academic_title ?? '—' }}</td>
                        <td class="px-4 py-3">{{ ucfirst($lecturer->status) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('master-data.lecturers.edit', $lecturer) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 hover:bg-slate-200">Ubah</a>
                                <form method="POST" action="{{ route('master-data.lecturers.destroy', $lecturer) }}" onsubmit="return confirm('Hapus dosen dan akunnya?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data dosen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $lecturers->links() }}</div>
@endsection
