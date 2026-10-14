@extends('layouts.guest')

@section('title', 'Reset Kata Sandi — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-xl font-bold">Reset Kata Sandi</h1>
    <p class="mb-5 text-sm text-slate-500">Masukkan kata sandi baru Anda.</p>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold">Kata Sandi Baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-semibold">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-500">
            Ubah Kata Sandi
        </button>
    </form>
@endsection
