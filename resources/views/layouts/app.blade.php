<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — MyCampus LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-lg font-bold text-indigo-700">MyCampus LMS</a>
                <span class="hidden rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 sm:inline-block">
                    {{ auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->label() : auth()->user()->role }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-slate-600 sm:block">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        Keluar
                    </button>
                </form>
            </div>
        </div>

        <nav class="mx-auto flex max-w-6xl flex-wrap gap-2 px-4 pb-3 text-sm" aria-label="Navigasi utama">
            @php $role = auth()->user()->role instanceof \App\Enums\UserRole ? auth()->user()->role->value : (string) auth()->user()->role; @endphp
            @if ($role === 'superadmin')
                <a href="{{ route('dashboard.superadmin') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Dashboard Superadmin</a>
                <a href="{{ route('dashboard.admin-academic') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Area Akademik</a>
                <a href="{{ route('dashboard.lecturer') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Area Dosen</a>
                <a href="{{ route('dashboard.student') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Area Mahasiswa</a>
                <a href="{{ route('master-data.faculties.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Master Data</a>
                <a href="{{ route('classes.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Kelas</a>
            @elseif ($role === 'admin_academic')
                <a href="{{ route('dashboard.admin-academic') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Dashboard Akademik</a>
                <a href="{{ route('master-data.faculties.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Master Data</a>
                <a href="{{ route('classes.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Kelas</a>
            @elseif ($role === 'lecturer')
                <a href="{{ route('dashboard.lecturer') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Dashboard Dosen</a>
                <a href="{{ route('classes.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Kelas Saya</a>
                <a href="{{ route('schedules.mine') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Jadwal Saya</a>
            @elseif ($role === 'student')
                <a href="{{ route('dashboard.student') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Dashboard Mahasiswa</a>
                <a href="{{ route('classes.index') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Kelas Saya</a>
                <a href="{{ route('schedules.mine') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100">Jadwal Saya</a>
            @endif
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-6">
        @if (session('status'))
            <div role="alert" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
