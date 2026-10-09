<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function asPbiAdministrator(): static
    {
        return $this->withSession([
            'pbi_admin_authenticated' => true,
            'pbi_admin_username' => 'admin',
        ]);
    }
}
