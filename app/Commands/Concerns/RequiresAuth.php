<?php

namespace App\Commands\Concerns;

use App\Services\ApiClient;
use App\Services\ConfigManager;
use App\Services\OutputFormatter;
use Illuminate\Http\Client\Response;

trait RequiresAuth
{
    protected ConfigManager $config;
    protected ApiClient $api;
    protected OutputFormatter $fmt;

    protected function bootServices(): void
    {
        $this->config = app(ConfigManager::class);
        $this->api    = app(ApiClient::class);
        $this->fmt    = app(OutputFormatter::class);
    }

    protected function guardAuth(): bool
    {
        if (! $this->config->isAuthenticated()) {
            $this->fmt->error(
                $this,
                'Not Authenticated',
                'No credentials found.',
                'Run `sshelf auth:login` first.'
            );
            return false;
        }
        return true;
    }

    protected function handleResponse(Response $response, string $notFoundHint = ''): bool
    {
        if ($response->successful()) {
            return true;
        }

        match ($response->status()) {
            401 => $this->fmt->error($this, 'Authentication Error',
                       'Your token is invalid or expired.',
                       'Run `sshelf auth:login` to re-authenticate.'),
            403 => $this->fmt->error($this, 'Forbidden',
                       'You do not have permission to perform this action.'),
            404 => $this->fmt->error($this, 'Not Found',
                       'Resource not found.',
                       $notFoundHint ?: 'Check the ID and try again.'),
            422 => $this->fmt->error($this, 'Validation Error',
                       collect($response->json('errors', []))
                           ->flatten()->first() ?? 'Invalid input.'),
            500 => $this->fmt->error($this, 'Server Error',
                       'Sshelf returned a 500. Check your server logs.'),
            default => $this->fmt->error($this, "HTTP {$response->status()}",
                       $response->json('message') ?? 'Unexpected error.'),
        };

        return false;
    }
}
