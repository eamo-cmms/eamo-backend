<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Provide safe dummy passwords for seeders during tests if not set
        config([
            'auth.seed_passwords.admin' => config('auth.seed_passwords.admin') ?? 'test-admin-pass',
            'auth.seed_passwords.manager' => config('auth.seed_passwords.manager') ?? 'test-manager-pass',
            'auth.seed_passwords.engineer' => config('auth.seed_passwords.engineer') ?? 'test-engineer-pass',
        ]);
    }
}
