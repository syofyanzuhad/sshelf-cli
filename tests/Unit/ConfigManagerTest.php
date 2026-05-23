<?php

use App\Services\ConfigManager;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sshelf_test_' . uniqid();
    $_SERVER['HOME'] = $this->tempDir;
    
    if (!is_dir($this->tempDir)) {
        mkdir($this->tempDir, 0700, true);
    }
});

afterEach(function () {
    File::deleteDirectory($this->tempDir);
});

test('it creates config directory and file', function () {
    new ConfigManager();
    
    expect(is_dir($this->tempDir . DIRECTORY_SEPARATOR . '.sshelf'))->toBeTrue();
    expect(file_exists($this->tempDir . DIRECTORY_SEPARATOR . '.sshelf' . DIRECTORY_SEPARATOR . 'config.json'))->toBeFalse();
});

test('it can set and get configuration', function () {
    $config = new ConfigManager();
    $config->set('url', 'https://api.sshelf.com');
    $config->set('token', 'secret-token');

    expect($config->get('url'))->toBe('https://api.sshelf.com');
    expect($config->get('token'))->toBe('secret-token');
});

test('it persists configuration to disk', function () {
    $config = new ConfigManager();
    $config->set('url', 'https://api.sshelf.com');
    
    $config2 = new ConfigManager();
    expect($config2->get('url'))->toBe('https://api.sshelf.com');
});

test('it sets correct file permissions', function () {
    $config = new ConfigManager();
    $config->set('url', 'https://api.sshelf.com');
    
    $path = $this->tempDir . DIRECTORY_SEPARATOR . '.sshelf' . DIRECTORY_SEPARATOR . 'config.json';
    expect(substr(sprintf('%o', fileperms($path)), -4))->toBe('0600');
});

test('it can check authentication status', function () {
    $config = new ConfigManager();
    expect($config->isAuthenticated())->toBeFalse();

    $config->set('url', 'https://api.sshelf.com');
    expect($config->isAuthenticated())->toBeFalse();

    $config->set('token', 'secret-token');
    expect($config->isAuthenticated())->toBeTrue();
});

test('it can forget a key', function () {
    $config = new ConfigManager();
    $config->set('key', 'value');
    expect($config->get('key'))->toBe('value');

    $config->forget('key');
    expect($config->get('key'))->toBeNull();
});

test('it can delete the config file', function () {
    $config = new ConfigManager();
    $config->set('key', 'value');
    
    $path = $this->tempDir . DIRECTORY_SEPARATOR . '.sshelf' . DIRECTORY_SEPARATOR . 'config.json';
    expect(file_exists($path))->toBeTrue();

    $config->delete();
    expect(file_exists($path))->toBeFalse();
    expect($config->all())->toBeEmpty();
});
