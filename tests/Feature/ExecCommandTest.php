<?php

use App\Services\ConfigManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->config = app(ConfigManager::class);
    $this->config->set('url', 'https://api.sshelf.com');
    $this->config->set('token', 'secret-token');
});

test('exec command works', function () {
    Http::fake([
        'api.sshelf.com/servers/1/execute' => Http::response([
            'output' => "Command Output Here",
            'exit_code' => 0
        ]),
    ]);

    $this->artisan('exec', ['server' => 1, 'cmd' => 'uptime'])
        ->expectsOutput('Command Output Here')
        ->assertExitCode(0);
});

test('qc:run command works', function () {
    Http::fake([
        'api.sshelf.com/quick-commands/5' => Http::response([
            'data' => [
                'id' => 5,
                'server_id' => 1,
                'command' => 'ls -la'
            ]
        ]),
        'api.sshelf.com/servers/1/execute' => Http::response([
            'output' => 'total 0',
            'exit_code' => 0
        ]),
    ]);

    $this->artisan('qc:run', ['id' => 5])
        ->expectsOutput('total 0')
        ->assertExitCode(0);
});
