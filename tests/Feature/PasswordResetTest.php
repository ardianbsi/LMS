<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_lupa_kata_sandi_dapat_ditampilkan(): void
    {
        $this->get('/lupa-kata-sandi')->assertOk();
    }

    public function test_tautan_reset_dikirim_untuk_email_terdaftar(): void
    {
        Notification::fake();

        User::factory()->create(['email' => 'user@mycampus.local']);

        $response = $this->post('/lupa-kata-sandi', ['email' => 'user@mycampus.local']);

        $response->assertSessionHas('status');
    }

    public function test_tautan_reset_ditolak_untuk_email_tidak_terdaftar(): void
    {
        $response = $this->post('/lupa-kata-sandi', ['email' => 'tidakada@mycampus.local']);

        $response->assertSessionHasErrors('email');
    }
}
