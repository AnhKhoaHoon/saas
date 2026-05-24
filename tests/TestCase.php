<?php

namespace Tests;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed Spatie roles and permissions for tests that refresh the database.
     */
    protected bool $seed = true;

    /**
     * Use the RBAC-only seeder so tests stay focused and deterministic.
     */
    protected string $seeder = PermissionSeeder::class;
}
