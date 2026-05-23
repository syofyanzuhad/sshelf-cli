<?php

use App\Services\ConfigManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->config = app(ConfigManager::class);
    $this->config->delete(); // Start clean
});

test('auth:login command works', function () {
    Http::fake([
        'api.sshelf.com/servers*' => Http::response(['data' => []]),
    ]);

    $this->artisan('auth:login')
        ->expectsQuestion('API Base URL', 'https://api.sshelf.com')
        ->expectsQuestion('Bearer Token', 'secret-token')
        ->expectsOutput('  ✓ Authenticated @ https://api.sshelf.com')
        ->assertExitCode(0);

    expect($this->config->get('url'))->toBe('https://api.sshelf.com');
    expect($this->config->get('token'))->toBe('secret-token');
});

test('auth:status command shows authenticated status', function () {
    $this->config->set('url', 'https://api.sshelf.com');
    $this->config->set('token', 'secret-token-long-enough');

    $this->artisan('auth:status')
        ->expectsOutput('  URL:   https://api.sshelf.com')
        ->expectsOutput('  Token: secret...ough')
        ->assertExitCode(0);
});

test('auth:logout command clears config', function () {
    $this->config->set('url', 'https://api.sshelf.com');
    $this->config->set('token', 'secret-token');

    $this->artisan('auth:logout')
        ->expectsOutput('  ✓ Credentials cleared.')
        ->assertExitCode(0);

    expect($this->config->isAuthenticated())->toBeFalse();
});
