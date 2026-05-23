<?php

use App\Services\ConfigManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->config = app(ConfigManager::class);
    $this->config->delete(); // Start clean
});

test('auth:login command works', function () {
    Http::fake([
        'sshelf.syofyanzuhad.dev/servers*' => Http::response(['data' => []]),
    ]);

    $this->artisan('auth:login')
        ->expectsQuestion('API Base URL', 'https://sshelf.syofyanzuhad.dev')
        ->expectsQuestion('Bearer Token', 'secret-token')
        ->expectsOutput('  ✓ Authenticated @ https://sshelf.syofyanzuhad.dev')
        ->assertExitCode(0);

    expect($this->config->get('url'))->toBe('https://sshelf.syofyanzuhad.dev');
    expect($this->config->get('token'))->toBe('secret-token');
});

test('auth:status command shows authenticated status', function () {
    $this->config->set('url', 'https://sshelf.syofyanzuhad.dev');
    $this->config->set('token', 'secret-token-long-enough');

    $this->artisan('auth:status')
        ->expectsOutput('  URL:   https://sshelf.syofyanzuhad.dev')
        ->expectsOutput('  Token: secret...ough')
        ->assertExitCode(0);
});

test('auth:logout command clears config', function () {
    $this->config->set('url', 'https://sshelf.syofyanzuhad.dev');
    $this->config->set('token', 'secret-token');

    $this->artisan('auth:logout')
        ->expectsOutput('  ✓ Credentials cleared.')
        ->assertExitCode(0);

    expect($this->config->isAuthenticated())->toBeFalse();
});
