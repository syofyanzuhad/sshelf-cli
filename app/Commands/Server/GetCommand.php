<?php

namespace App\Commands\Server;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class GetCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'server:get
        {id : Server ID}
        {--json : Output as JSON}';

    protected $description = 'Show a single server';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $id       = $this->argument('id');
        $response = $this->api->get("/servers/{$id}");

        if (! $this->handleResponse($response, "Run `sshelf server:list` to see available servers.")) {
            return self::FAILURE;
        }

        $s = $response->json('data') ?? $response->json();

        $this->newLine();
        $this->line("  <fg=cyan>Server #{$s['id']}</>");
        $this->fmt->record($this, [
            'name'      => $s['name'],
            'host'      => $s['host'],
            'port'      => $s['port'] ?? 22,
            'username'  => $s['username'],
            'auth_type' => $s['auth_type'],
            'group'     => $s['group'] ?? '—',
            'notes'     => $s['notes'] ?? '—',
        ], $this->option('json'));
        $this->newLine();

        return self::SUCCESS;
    }
}
