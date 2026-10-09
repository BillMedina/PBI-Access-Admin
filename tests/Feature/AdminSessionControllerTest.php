<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSessionControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('access-control.admin.username', 'admin');
        config()->set('access-control.admin.password_hash', Hash::make('correct-password'));
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $response = $this->get(route('menu-items.index'));

        $response->assertRedirect(route('admin-session.create'));
    }

    public function test_valid_credentials_create_an_administrator_session(): void
    {
        $response = $this->post(route('admin-session.store'), [
            'username' => 'ADMIN',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('security-users.index'));
        $response->assertSessionHas('pbi_admin_authenticated', true);
        $response->assertSessionHas('pbi_admin_username', 'admin');
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->from(route('admin-session.create'))->post(route('admin-session.store'), [
            'username' => 'admin',
            'password' => 'incorrect-password',
        ]);

        $response->assertRedirect(route('admin-session.create'));
        $response->assertSessionHasErrors('username');
        $response->assertSessionMissing('pbi_admin_authenticated');
    }
}
