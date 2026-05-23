<?php

use App\Services\ConfigManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->config = app(ConfigManager::class);
    $this->config->set('url', 'https://sshelf.syofyanzuhad.dev');
    $this->config->set('token', 'secret-token');
});

test('server:list command displays servers', function () {
    Http::fake([
        'sshelf.syofyanzuhad.dev/servers*' => Http::response([
            'data' => [
                ['id' => 1, 'name' => 'Web 1', 'host' => '1.1.1.1', 'port' => 22, 'username' => 'root', 'auth_type' => 'key', 'group' => null],
                ['id' => 2, 'name' => 'Web 2', 'host' => '2.2.2.2', 'port' => 22, 'username' => 'ubuntu', 'auth_type' => 'password', 'group' => null],
            ]
        ]),
    ]);

    $this->artisan('server:list')
        ->expectsTable(['ID', 'Name', 'Host', 'Port', 'Username', 'Auth Type', 'Group'], [
            [1, 'Web 1', '1.1.1.1', 22, 'root', 'key', '—'],
            [2, 'Web 2', '2.2.2.2', 22, 'ubuntu', 'password', '—'],
        ])
        ->assertExitCode(0);
});

test('server:add command works', function () {
    Http::fake([
        'sshelf.syofyanzuhad.dev/servers' => Http::response(['data' => ['id' => 123]], 201),
    ]);

    $this->artisan('server:add', [
        '--name' => 'New Server',
        '--host' => '4.4.4.4',
        '--username' => 'admin',
    ])->expectsOutput('  ✓ Server "New Server" created (ID: 123)')
      ->assertExitCode(0);
});

test('server:get command displays server details', function () {
    Http::fake([
        'sshelf.syofyanzuhad.dev/servers/1' => Http::response([
            'data' => [
                'id' => 1, 
                'name' => 'Web 1', 
                'host' => '1.1.1.1', 
                'port' => 22, 
                'username' => 'root', 
                'auth_type' => 'key', 
                'group' => 'Web', 
                'notes' => 'Test notes'
            ]
        ]),
    ]);

    $this->artisan('server:get', ['id' => 1])
        ->expectsOutput('  Server #1')
        ->assertExitCode(0);
});

test('server:delete command works', function () {
    Http::fake([
        'sshelf.syofyanzuhad.dev/servers/1' => Http::sequence()
            ->push(['data' => ['id' => 1, 'name' => 'Web 1']], 200)
            ->push([], 204),
    ]);

    $this->artisan('server:delete', ['id' => 1])
        ->expectsConfirmation('Delete server "Web 1" (#1)?', 'yes')
        ->expectsOutput('  ✓ Server #1 deleted.')
        ->assertExitCode(0);
});
