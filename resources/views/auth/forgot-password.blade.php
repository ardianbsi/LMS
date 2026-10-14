@extends('layouts.guest')

@section('title', 'Lupa Kata Sandi — MyCampus LMS')

@section('content')
    <h1 class="mb-1 text-xl font-bold">Lupa Kata Sandi</h1>
    <p class="mb-5 text-sm text-slate-500">Masukkan email Anda. Kami akan mengirimkan tautan reset kata sandi.</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-500">
            Kirim Tautan Reset
        </button>
    </form>

    <p class="mt-4 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:underline">Kembali ke halaman masuk</a>
    </p>
@endsection
