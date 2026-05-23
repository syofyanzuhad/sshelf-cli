<?php

namespace App\Services;

use Illuminate\Console\Command;

class OutputFormatter
{
    public function table(
        Command $command,
        array $headers,
        array $rows,
        bool $json = false
    ): void {
        if ($json) {
            $keyed = array_map(
                fn($row) => array_combine(
                    array_map(fn($h) => strtolower(str_replace(' ', '_', $h)), $headers),
                    $row
                ),
                $rows
            );
            $command->line(json_encode($keyed, JSON_PRETTY_PRINT));
            return;
        }

        $command->table($headers, $rows);
    }

    public function record(Command $command, array $data, bool $json = false): void
    {
        if ($json) {
            $command->line(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        foreach ($data as $key => $value) {
            $label = str_pad(ucfirst(str_replace('_', ' ', $key)) . ':', 14);
            $command->line("  <fg=gray>{$label}</> {$value}");
        }
    }

    public function error(Command $command, string $type, string $message, string $hint = ''): void
    {
        $command->newLine();
        $command->line("  <fg=red>✗ {$type}:</> {$message}");
        if ($hint) {
            $command->line("  <fg=yellow>→ Hint:</> {$hint}");
        }
        $command->newLine();
    }

    public function success(Command $command, string $message): void
    {
        $command->line("  <fg=green>✓</> {$message}");
    }
}
