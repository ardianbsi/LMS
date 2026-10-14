@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Dashboard Superadmin</h1>
    <p class="mb-6 text-sm text-slate-500">Halo, {{ auth()->user()->name }}. Kelola konfigurasi, akun, role, dan audit sistem dari sini.</p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pengguna</p>
            <p class="text-2xl font-bold">—</p>
            <p class="text-xs text-slate-400">Modul manajemen akun hadir di fase berikutnya.</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Role &amp; Akses</p>
            <p class="text-2xl font-bold">4</p>
            <p class="text-xs text-slate-400">Superadmin, Akademik, Dosen, Mahasiswa.</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Audit</p>
            <p class="text-2xl font-bold">—</p>
            <p class="text-xs text-slate-400">Log audit hadir di fase berikutnya.</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Status Sistem</p>
            <p class="text-2xl font-bold text-emerald-600">Baik</p>
            <p class="text-xs text-slate-400">Fase 1: fondasi &amp; autentikasi.</p>
        </div>
    </div>
@endsection
