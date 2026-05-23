<?php

namespace App\Commands\Server;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class EditCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'server:edit
        {id          : Server ID}
        {--name=     : New name}
        {--host=     : New host}
        {--port=     : New port}
        {--username= : New username}
        {--auth-type=: New auth type (password|key)}
        {--group=    : New group}
        {--notes=    : New notes}';

    protected $description = 'Edit an existing server';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $id = $this->argument('id');

        // Fetch current values for merge
        $current = $this->api->get("/servers/{$id}");
        if (! $this->handleResponse($current, "Run `sshelf server:list` to see available IDs.")) {
            return self::FAILURE;
        }

        $existing = $current->json('data') ?? $current->json();

        // Merge: only send flags that were explicitly passed
        $payload = array_filter([
            'name'      => $this->option('name')      ?? $existing['name'],
            'host'      => $this->option('host')      ?? $existing['host'],
            'port'      => $this->option('port')      ? (int)$this->option('port') : $existing['port'],
            'username'  => $this->option('username')  ?? $existing['username'],
            'auth_type' => $this->option('auth-type') ?? $existing['auth_type'],
            'group'     => $this->option('group')     ?? $existing['group'],
            'notes'     => $this->option('notes')     ?? $existing['notes'],
        ], fn($v) => ! is_null($v));

        $response = $this->api->put("/servers/{$id}", $payload);
        if (! $this->handleResponse($response)) return self::FAILURE;

        $this->newLine();
        $this->fmt->success($this, "Server #{$id} updated.");
        $this->newLine();

        return self::SUCCESS;
    }
}
