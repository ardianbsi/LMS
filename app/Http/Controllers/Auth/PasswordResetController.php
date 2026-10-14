<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan form lupa kata sandi.
     */
    public function requestForm(): View|RedirectResponse
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'string', 'email:filter', 'max:255']],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
            ]
        );

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'Tautan reset kata sandi telah dikirim ke email Anda.')
            : back()->withErrors(['email' => 'Email tidak ditemukan.']);
    }

    /**
     * Tampilkan form reset kata sandi.
     */
    public function resetForm(string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request()->query('email'),
        ]);
    }

    /**
     * Proses reset kata sandi.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'token' => ['required', 'string'],
                'email' => ['required', 'string', 'email:filter', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'password.required' => 'Kata sandi baru wajib diisi.',
                'password.min' => 'Kata sandi minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            ]
        );

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan login.')
            : back()->withErrors(['email' => 'Token reset tidak valid atau sudah kedaluwarsa.']);
    }
}
