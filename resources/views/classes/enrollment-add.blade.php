@extends('layouts.app')

@section('title', 'Tambah Enrollment — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Daftarkan Mahasiswa</h1>
    <p class="mb-4 text-sm text-slate-500">{{ $courseClass->displayName() }} — sistem menolak duplikat dan bentrok jadwal otomatis.</p>

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
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3 font-mono">{{ $student->nim }}</td>
                        <td class="px-4 py-3">{{ $student->user?->name }}</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('classes.enrollments.store', $courseClass) }}" class="inline">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-white hover:bg-indigo-500">Daftarkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">Semua mahasiswa aktif sudah terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $students->links() }}</div>

    <p class="mt-4"><a href="{{ route('classes.enrollments.index', $courseClass) }}" class="text-sm font-semibold text-indigo-600 hover:underline">← Kembali ke daftar enrollment</a></p>
@endsection
