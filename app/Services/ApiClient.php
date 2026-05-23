<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    protected string $baseUrl;
    protected string $token;

    public function __construct(protected ConfigManager $config)
    {
        $this->baseUrl = rtrim($config->get('url', ''), '/');
        $this->token   = $config->get('token', '');
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->token)
            ->acceptJson()
            ->timeout($this->config->get('timeout', 30));
    }

    public function get(string $endpoint, array $query = []): Response
    {
        return $this->client()->get($endpoint, $query);
    }

    public function post(string $endpoint, array $data = []): Response
    {
        return $this->client()->post($endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): Response
    {
        return $this->client()->put($endpoint, $data);
    }

    public function delete(string $endpoint): Response
    {
        return $this->client()->delete($endpoint);
    }
}
