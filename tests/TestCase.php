<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mindigo\Auth\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new \RuntimeException(
                'Unsafe test database configuration: PHPUnit must use sqlite :memory:.'
            );
        }
    }

    protected function createUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        if (! $user instanceof User) {
            throw new \LogicException('The user factory must create exactly one User model.');
        }

        return $user;
    }
}
