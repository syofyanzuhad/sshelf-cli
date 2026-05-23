<?php

namespace App\Commands\Key;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class AddCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'key:add
        {--name=       : Key name (required)}
        {--public-key= : Public key string or @/path/to/file.pub}';

    protected $description = 'Add a new SSH public key';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $name      = $this->option('name') ?? $this->ask('Key name');
        $rawKey    = $this->option('public-key') ?? $this->ask('Public key (or @filepath)');

        // @FILE syntax
        if (str_starts_with($rawKey, '@')) {
            $filepath = substr($rawKey, 1);
            if (! file_exists($filepath)) {
                $this->fmt->error($this, 'File Not Found', "Cannot read: {$filepath}");
                return self::FAILURE;
            }
            $rawKey = trim(file_get_contents($filepath));
        }

        // Soft validation
        $validPrefixes = ['ssh-rsa', 'ssh-ed25519', 'ecdsa-sha2-', 'sk-'];
        $looksValid    = collect($validPrefixes)->contains(fn($p) => str_starts_with($rawKey, $p));
        if (! $looksValid) {
            $this->line('  <fg=yellow>⚠ Warning:</> This may not be a valid SSH public key.');
        }

        $response = $this->api->post('/ssh-keys', [
            'name'       => $name,
            'public_key' => $rawKey,
        ]);

        if (! $this->handleResponse($response)) return self::FAILURE;

        $id = ($response->json('data') ?? $response->json())['id'];
        $this->fmt->success($this, "Key \"{$name}\" created (ID: {$id})");

        return self::SUCCESS;
    }
}
