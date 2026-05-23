<?php

namespace App\Commands\Tag;

use App\Commands\Concerns\RequiresAuth;
use LaravelZero\Framework\Commands\Command;

class AddCommand extends Command
{
    use RequiresAuth;

    protected $signature = 'tag:add
        {--name=  : Tag name (required)}
        {--color= : Hex color e.g. #FF4444}';

    protected $description = 'Add a new tag';

    public function handle(): int
    {
        $this->bootServices();
        if (! $this->guardAuth()) return self::FAILURE;

        $name = $this->option('name') ?? $this->ask('Tag name');
        $color = $this->option('color') ?? $this->ask('Tag color (hex)', '#666666');

        if ($color && ! preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)) {
            $this->fmt->error($this, 'Validation Error', 'Color must be a hex code like #FF4444.');
            return self::FAILURE;
        }

        // Normalize #RGB → #RRGGBB
        if ($color && strlen($color) === 4) {
            $color = '#' . $color[1].$color[1] . $color[2].$color[2] . $color[3].$color[3];
        }

        $response = $this->api->post('/tags', [
            'name'  => $name,
            'color' => $color,
        ]);

        if (! $this->handleResponse($response)) return self::FAILURE;

        $id = ($response->json('data') ?? $response->json())['id'];
        $this->fmt->success($this, "Tag \"{$name}\" created (ID: {$id})");

        return self::SUCCESS;
    }
}
