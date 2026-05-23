<?php

namespace App\Commands\Server;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'server:list
        {--group= : Filter by group name}
        {--json   : Output as JSON}';

    protected $description = 'List all servers';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $response = $this->api->get('/servers');
        if (! $this->handleResponse($response)) return self::FAILURE;

        $servers = collect($response->json('data') ?? $response->json());

        if ($group = $this->option('group')) {
            $servers = $servers->filter(fn($s) => $s['group'] === $group);
        }

        $rows = $servers->map(fn($s) => [
            $s['id'],
            $s['name'],
            $s['host'],
            $s['port'] ?? 22,
            $s['username'],
            $s['auth_type'],
            $s['group'] ?? '—',
        ])->values()->toArray();

        $this->fmt->table(
            $this,
            ['ID', 'Name', 'Host', 'Port', 'Username', 'Auth Type', 'Group'],
            $rows,
            $this->option('json')
        );

        return self::SUCCESS;
    }
}
