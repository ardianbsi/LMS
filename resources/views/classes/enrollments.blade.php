@extends('layouts.app')

@section('title', 'Mahasiswa Kelas — MyCampus LMS')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Mahasiswa Kelas</h1>
            <p class="text-sm text-slate-500">{{ $courseClass->displayName() }} — {{ $courseClass->enrollments->count() }} terdaftar</p>
        </div>
        <a href="{{ route('classes.enrollments.candidates', $courseClass) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-500">+ Daftarkan Mahasiswa</a>
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
                    <th class="px-4 py-3">NIM</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courseClass->enrollments as $enrollment)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-mono">{{ $enrollment->student?->nim }}</td>
                        <td class="px-4 py-3">{{ $enrollment->student?->displayName() }}</td>
                        <td class="px-4 py-3">{{ ucfirst($enrollment->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('classes.enrollments.destroy', [$courseClass, $enrollment]) }}" onsubmit="return confirm('Hapus enrollment ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-red-700 hover:bg-red-200">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada mahasiswa terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-4"><a href="{{ route('classes.show', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke detail kelas</a></p>
@endsection
