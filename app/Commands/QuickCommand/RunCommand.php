<?php

namespace App\Commands\QuickCommand;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class RunCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'qc:run
        {id    : Quick command ID}
        {--json: Output as JSON}';

    protected $description = 'Run a saved quick command on its server';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        // Step 1: fetch the quick command
        $qc = $this->api->get("/quick-commands/{$this->argument('id')}");
        if (! $this->handleResponse($qc, 'Run `sshelf qc:list` to see available IDs.')) {
            return self::FAILURE;
        }

        $record    = $qc->json('data') ?? $qc->json();
        $serverId  = $record['server_id'];
        $command   = $record['command'];

        $this->line("  <fg=gray>Running:</> {$command}");
        $this->line("  <fg=gray>On server ID:</> {$serverId}");
        $this->newLine();

        // Step 2: execute
        $response = $this->api->post("/servers/{$serverId}/execute", [
            'command' => $command,
        ]);

        if (! $this->handleResponse($response)) return self::FAILURE;

        $result   = $response->json();
        $exitCode = $result['exit_code'] ?? 0;

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $this->line($result['output'] ?? '');
            $this->newLine();
            $color = $exitCode === 0 ? 'green' : 'red';
            $this->line("  <fg={$color}>Exit code: {$exitCode}</>");
        }

        return $exitCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}
