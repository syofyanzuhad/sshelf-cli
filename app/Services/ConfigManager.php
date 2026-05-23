<?php

namespace App\Services;

class ConfigManager
{
    protected string $path;
    protected array $data = [];

    public function __construct()
    {
        $home = $_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '/tmp';
        $dir  = $home . DIRECTORY_SEPARATOR . '.sshelf';

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $this->path = $dir . DIRECTORY_SEPARATOR . 'config.json';
        $this->load();
    }

    protected function load(): void
    {
        if (file_exists($this->path)) {
            $this->data = json_decode(file_get_contents($this->path), true) ?? [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
        $this->save();
    }

    public function all(): array
    {
        return $this->data;
    }

    public function forget(string $key): void
    {
        unset($this->data[$key]);
        $this->save();
    }

    public function delete(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        $this->data = [];
    }

    public function isAuthenticated(): bool
    {
        return ! empty($this->data['url']) && ! empty($this->data['token']);
    }

    protected function save(): void
    {
        file_put_contents(
            $this->path,
            json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        // Restrict to owner read/write only
        chmod($this->path, 0600);
    }
}
