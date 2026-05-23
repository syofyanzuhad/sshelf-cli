<?php

namespace App\Commands\Key;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'key:list
        {--json : Output as JSON}';

    protected $description = 'List all SSH keys';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $response = $this->api->get('/ssh-keys');
        if (! $this->handleResponse($response)) return self::FAILURE;

        $keys = collect($response->json('data') ?? $response->json());

        $rows = $keys->map(fn($k) => [
            $k['id'],
            $k['name'],
            substr($k['public_key'], 0, 30) . '...',
        ])->toArray();

        $this->fmt->table(
            $this,
            ['ID', 'Name', 'Public Key'],
            $rows,
            $this->option('json')
        );

        return self::SUCCESS;
    }
}
