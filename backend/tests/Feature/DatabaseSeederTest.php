<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('seeds default roles and accounts without errors', function () {
    $exitCode = Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    expect($exitCode)->toBe(0);
});
