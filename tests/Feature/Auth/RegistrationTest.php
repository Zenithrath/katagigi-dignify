<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled(): void
    {
        // Registrasi publik ditutup: akun hanya dibuat oleh manajemen.
        $this->get('/register')->assertNotFound();
    }
}
