@extends('layouts.guest')

@section('title', 'Masuk — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-xl font-bold">Masuk</h1>
    <p class="mb-5 text-sm text-slate-500">Gunakan email dan kata sandi akun kampus Anda.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold">Kata Sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="font-semibold text-indigo-600 hover:underline">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-500">
            Masuk
        </button>
    </form>
@endsection
