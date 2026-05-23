<?php

namespace App\Commands\Auth;

use App\Services\ConfigManager;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    protected $signature = 'auth:login
        {--url= : API base URL}
        {--token= : Bearer token}';

    protected $description = 'Authenticate with your Sshelf instance';

    public function handle(ConfigManager $config): int
    {
        $this->newLine();

        $url = $this->option('url')
            ?? $this->ask('API Base URL', $config->get('url', 'https://'));

        $token = $this->option('token')
            ?? $this->secret('Bearer Token');

        $url = rtrim($url, '/');

        $this->line('  <fg=gray>Verifying credentials...</>');

        try {
            $response = Http::baseUrl($url)
                ->withToken($token)
                ->acceptJson()
                ->timeout(10)
                ->get('/servers', ['per_page' => 1]);
        } catch (\Exception $e) {
            $this->newLine();
            $this->line('  <fg=red>✗ Connection Failed:</> ' . $e->getMessage());
            $this->line('  <fg=yellow>→ Hint:</> Check the URL and your network connection.');
            $this->newLine();
            return self::FAILURE;
        }

        if ($response->status() === 401) {
            $this->newLine();
            $this->line('  <fg=red>✗ Authentication Failed:</> Invalid token.');
            $this->line('  <fg=yellow>→ Hint:</> Generate a new token from your Sshelf dashboard.');
            $this->newLine();
            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->newLine();
            $this->line("  <fg=red>✗ HTTP {$response->status()}:</> Could not connect.");
            $this->newLine();
            return self::FAILURE;
        }

        $config->set('url', $url);
        $config->set('token', $token);

        $this->newLine();
        $this->line("  <fg=green>✓</> Authenticated @ <fg=cyan>{$url}</>");
        $this->newLine();

        return self::SUCCESS;
    }
}
