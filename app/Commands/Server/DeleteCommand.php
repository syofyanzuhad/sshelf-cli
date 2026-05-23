<?php

namespace App\Commands\Server;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class DeleteCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'server:delete
        {id       : Server ID}
        {--force  : Skip confirmation prompt}';

    protected $description = 'Delete a server';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $id = $this->argument('id');

        // Fetch name for confirmation message
        $current  = $this->api->get("/servers/{$id}");
        if (! $this->handleResponse($current)) return self::FAILURE;

        $name = ($current->json('data') ?? $current->json())['name'];

        if (! $this->option('force')) {
            $confirmed = $this->confirm("Delete server \"{$name}\" (#{$id})?", false);
            if (! $confirmed) {
                $this->line('  Aborted.');
                return self::SUCCESS;
            }
        }

        $response = $this->api->delete("/servers/{$id}");
        if (! $this->handleResponse($response)) return self::FAILURE;

        $this->newLine();
        $this->fmt->success($this, "Server #{$id} deleted.");
        $this->newLine();

        return self::SUCCESS;
    }
}
