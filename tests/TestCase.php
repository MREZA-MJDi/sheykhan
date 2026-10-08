<?php

namespace Tests;

use Illuminate\\Foundation\\Testing\\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Force the framework into its test environment before Laravel boots.
     *
     * Some local Windows shells export APP_ENV from a developer's .env or
     * process environment. If that leaks into the test bootstrap, Laravel's
     * CSRF middleware can run during feature tests and return 419 for normal
     * simulated form submissions.
     */
    protected function setUp(): void
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        parent::setUp();
    }
}
