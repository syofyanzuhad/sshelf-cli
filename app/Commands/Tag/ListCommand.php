<?php

namespace App\Commands\Tag;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'tag:list
        {--json : Output as JSON}';

    protected $description = 'List all tags';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $response = $this->api->get('/tags');
        if (! $this->handleResponse($response)) return self::FAILURE;

        $tags = collect($response->json('data') ?? $response->json());

        $rows = $tags->map(fn($t) => [
            $t['id'],
            $t['name'],
            $t['color'],
        ])->toArray();

        $this->fmt->table(
            $this,
            ['ID', 'Name', 'Color'],
            $rows,
            $this->option('json')
        );

        return self::SUCCESS;
    }
}
