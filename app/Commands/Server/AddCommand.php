<?php

namespace App\Commands\Server;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class AddCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'server:add
        {--name=     : Server name (required)}
        {--host=     : Hostname or IP (required)}
        {--port=22   : SSH port (1-65535)}
        {--username=root : SSH username}
        {--auth-type=key : Authentication type (password|key)}
        {--group=    : Group label}
        {--notes=    : Free-text notes}';

    protected $description = 'Add a new server';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        // --- Validation ---
        $name = $this->option('name') ?? $this->ask('Server name');
        $host = $this->option('host') ?? $this->ask('Host / IP');

        if (empty($name)) {
            $this->fmt->error($this, 'Validation Error', '--name is required.');
            return self::FAILURE;
        }
        if (empty($host)) {
            $this->fmt->error($this, 'Validation Error', '--host is required.');
            return self::FAILURE;
        }

        $port = (int) $this->option('port');
        if ($port < 1 || $port > 65535) {
            $this->fmt->error($this, 'Validation Error', 'Port must be between 1 and 65535.');
            return self::FAILURE;
        }

        $authType = $this->option('auth-type');
        if (! in_array($authType, ['password', 'key'])) {
            $this->fmt->error($this, 'Validation Error', '--auth-type must be "password" or "key".');
            return self::FAILURE;
        }

        // Strip accidental protocol prefix
        $host = preg_replace('#^(ssh|http|https)://#', '', $host);

        // --- API Call ---
        $response = $this->api->post('/servers', [
            'name'      => $name,
            'host'      => $host,
            'port'      => $port,
            'username'  => $this->option('username'),
            'auth_type' => $authType,
            'group'     => $this->option('group'),
            'notes'     => $this->option('notes'),
        ]);

        if (! $this->handleResponse($response)) return self::FAILURE;

        $id = ($response->json('data') ?? $response->json())['id'];
        $this->newLine();
        $this->fmt->success($this, "Server \"{$name}\" created (ID: {$id})");
        $this->newLine();

        return self::SUCCESS;
    }
}
