@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
    <h1 class="mb-1 text-2xl font-bold">Dashboard Dosen</h1>
    <p class="mb-6 text-sm text-slate-500">Halo, {{ auth()->user()->name }}. Kelas yang Anda ampu, jadwal, dan tugas akan tampil di sini.</p>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <p class="text-sm text-slate-500">Kelas Diampu</p>
        <p class="text-2xl font-bold">—</p>
        <p class="text-xs text-slate-400">Belum ada kelas. Penugasan kelas hadir di Fase 3.</p>
    </div>
@endsection
