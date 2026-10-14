@extends('layouts.app')

@section('title', 'Dashboard Admin Akademik')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Dashboard Admin Akademik</h1>
    <p class="mb-6 text-sm text-slate-500">Halo, {{ auth()->user()->name }}. Kelola master data akademik, kelas, jadwal, dan laporan dari sini.</p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Master Data</p>
            <p class="text-2xl font-bold">—</p>
            <p class="text-xs text-slate-400">Fakultas, prodi, MK, ruang (Fase 2).</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Kelas &amp; Jadwal</p>
            <p class="text-2xl font-bold">—</p>
            <p class="text-xs text-slate-400">Enrollment dan jadwal (Fase 3).</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Laporan</p>
            <p class="text-2xl font-bold">—</p>
            <p class="text-xs text-slate-400">Rekap akademik (Fase 8).</p>
        </div>
    </div>
@endsection
