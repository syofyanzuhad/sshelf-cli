<?php

namespace App\Commands\Auth;

use App\Services\ConfigManager;
use LaravelZero\Framework\Commands\Command;

class LogoutCommand extends Command
{
    protected $signature = 'auth:logout';
    protected $description = 'Remove stored credentials';

    public function handle(ConfigManager $config): int
    {
        $config->delete();
        $this->newLine();
        $this->line('  <fg=green>✓</> Credentials cleared.');
        $this->newLine();
        return self::SUCCESS;
    }
}
