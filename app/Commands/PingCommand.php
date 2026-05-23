<?php

namespace App\Commands;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class PingCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'ping';
    protected $description = 'Check connectivity to your Sshelf instance';

    public function handle(): int
    {
        $this->bootServices();

        if (! $this->guardAuth()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('  <fg=gray>Pinging ' . $this->config->get('url') . '...</>');

        $start = microtime(true);

        try {
            $response = $this->api->get('/servers', ['per_page' => 1]);
        } catch (\Exception $e) {
            $this->fmt->error($this, 'Connection Failed', $e->getMessage());
            return self::FAILURE;
        }

        $ms = round((microtime(true) - $start) * 1000);

        if (! $response->successful()) {
            $this->handleResponse($response);
            return self::FAILURE;
        }

        $this->line("  <fg=green>✓</> Connected ({$ms}ms)");
        $this->newLine();

        return self::SUCCESS;
    }
}
