<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
            \App\Http\Controllers\Api\RoleController::ensureDefaultPermissions();
        }
    }
}
