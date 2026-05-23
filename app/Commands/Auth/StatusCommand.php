<?php

namespace App\Commands\Auth;

use App\Services\ConfigManager;
use LaravelZero\Framework\Commands\Command;

class StatusCommand extends Command
{
    protected $signature = 'auth:status';
    protected $description = 'Show current authentication status';

    public function handle(ConfigManager $config): int
    {
        $this->newLine();

        if (! $config->isAuthenticated()) {
            $this->line('  <fg=red>✗ Not authenticated.</>');
            $this->line('  <fg=yellow>→</> Run `sshelf auth:login` to get started.');
            $this->newLine();
            return self::FAILURE;
        }

        $token = $config->get('token');
        $preview = substr($token, 0, 6) . '...' . substr($token, -4);

        $this->line('  <fg=gray>URL:  </> ' . $config->get('url'));
        $this->line('  <fg=gray>Token:</> ' . $preview);
        $this->newLine();

        return self::SUCCESS;
    }
}
