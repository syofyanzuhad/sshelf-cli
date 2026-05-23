<?php

namespace App\Commands\QuickCommand;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'qc:list
        {--json : Output as JSON}';

    protected $description = 'List all quick commands';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $response = $this->api->get('/quick-commands');
        if (! $this->handleResponse($response)) return self::FAILURE;

        $qcs = collect($response->json('data') ?? $response->json());

        $rows = $qcs->map(fn($q) => [
            $q['id'],
            $q['name'],
            $q['server_id'],
            $q['command'],
        ])->toArray();

        $this->fmt->table(
            $this,
            ['ID', 'Name', 'Server ID', 'Command'],
            $rows,
            $this->option('json')
        );

        return self::SUCCESS;
    }
}
