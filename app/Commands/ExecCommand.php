<?php

namespace App\Commands;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class ExecCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'exec
        {server            : Server ID}
        {cmd?             : Command to run (or use --stdin)}
        {--stdin           : Read command from stdin}
        {--json            : Output as JSON}
        {--timeout=30      : Request timeout in seconds}';

    protected $description = 'Execute a command on a remote server';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $serverId = $this->argument('server');

        // Resolve the command string
        if ($this->option('stdin')) {
            $command = trim(stream_get_contents(STDIN));
            if (empty($command)) {
                $this->fmt->error($this, 'Input Error', 'No command received from stdin.');
                return self::FAILURE;
            }
        } else {
            $command = $this->argument('cmd');
            if (empty($command)) {
                $this->fmt->error(
                    $this,
                    'Missing Argument',
                    'Provide a command or use --stdin.',
                    'Example: sshelf exec 1 "uptime"'
                );
                return self::FAILURE;
            }
        }

        // Set dynamic timeout from flag
        $this->config->set('timeout', (int) $this->option('timeout'));

        try {
            $response = $this->api->post("/servers/{$serverId}/execute", [
                'command' => $command,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->fmt->error(
                $this,
                'Timeout',
                "Command timed out after {$this->option('timeout')}s.",
                'The command may still be running on the server.'
            );
            return self::FAILURE;
        }

        if (! $this->handleResponse(
            $response,
            "Run `sshelf server:list` to see available server IDs."
        )) {
            return self::FAILURE;
        }

        $result   = $response->json();
        $output   = $result['output'] ?? '';
        $exitCode = $result['exit_code'] ?? 0;

        if ($this->option('json')) {
            $this->line(json_encode([
                'server_id' => (int) $serverId,
                'command'   => $command,
                'output'    => $output,
                'exit_code' => $exitCode,
            ], JSON_PRETTY_PRINT));
        } else {
            if (! empty($output)) {
                $this->line($output);
                $this->newLine();
            }

            if ($exitCode !== 0) {
                $this->line("  <fg=red>Exit code: {$exitCode}</>");
            } else {
                $this->line("  <fg=green>Exit code: 0</>");
            }
        }

        // Restore default timeout
        $this->config->set('timeout', 30);

        // Passthrough exit code for scripting
        return $exitCode;
    }
}
