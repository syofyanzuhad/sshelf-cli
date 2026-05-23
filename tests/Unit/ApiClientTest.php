<?php

use App\Services\ApiClient;
use App\Services\ConfigManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->config = Mockery::mock(ConfigManager::class);
    $this->config->shouldReceive('get')->with('url', '')->andReturn('https://api.sshelf.com');
    $this->config->shouldReceive('get')->with('token', '')->andReturn('secret-token');
    $this->config->shouldReceive('get')->with('timeout', 30)->andReturn(30);

    $this->client = new ApiClient($this->config);
});

test('it makes GET requests with proper headers', function () {
    Http::fake([
        'api.sshelf.com/*' => Http::response(['status' => 'ok']),
    ]);

    $response = $this->client->get('ping');

    expect($response->json())->toBe(['status' => 'ok']);
    
    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.sshelf.com/ping' &&
               $request->hasHeader('Authorization', 'Bearer secret-token') &&
               $request->hasHeader('Accept', 'application/json');
    });
});

test('it makes POST requests', function () {
    Http::fake();

    $this->client->post('servers', ['name' => 'Test Server']);

    Http::assertSent(function ($request) {
        return $request->method() === 'POST' &&
               $request->url() === 'https://api.sshelf.com/servers' &&
               $request['name'] === 'Test Server';
    });
});

test('it makes PUT requests', function () {
    Http::fake();

    $this->client->put('servers/1', ['name' => 'Updated Server']);

    Http::assertSent(function ($request) {
        return $request->method() === 'PUT' &&
               $request->url() === 'https://api.sshelf.com/servers/1' &&
               $request['name'] === 'Updated Server';
    });
});

test('it makes DELETE requests', function () {
    Http::fake();

    $this->client->delete('servers/1');

    Http::assertSent(function ($request) {
        return $request->method() === 'DELETE' &&
               $request->url() === 'https://api.sshelf.com/servers/1';
    });
});
