@extends('layouts.app')

@section('title', 'Fakultas — Master Data')

@section('content')
    @include('master-data._nav')

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Fakultas</h1>
            <p class="text-sm text-slate-500">Kelola data fakultas. Data yang sudah direferensikan tidak dapat dihapus — nonaktifkan saja.</p>
        </div>
        <a href="{{ route('master-data.faculties.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Tambah Fakultas</a>
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

    <form method="GET" action="{{ route('master-data.faculties.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode/nama..."
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($faculties as $faculty)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-mono">{{ $faculty->code }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('master-data.faculties.show', $faculty) }}" class="font-semibold text-indigo-600 hover:underline">{{ $faculty->name }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $faculty->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $faculty->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('master-data.faculties.edit', $faculty) }}" class="rounded-lg bg-slate-100 px-3 py-1.5 hover:bg-slate-200">Ubah</a>
                                <form method="POST" action="{{ route('master-data.faculties.toggle', $faculty) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg bg-amber-100 px-3 py-1.5 text-amber-800 hover:bg-amber-200">
                                        {{ $faculty->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('master-data.faculties.destroy', $faculty) }}" onsubmit="return confirm('Hapus fakultas ini? Hanya bisa jika belum memiliki prodi.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada data fakultas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $faculties->links() }}</div>
@endsection
