<?php

use App\Services\OutputFormatter;
use Illuminate\Console\Command;

beforeEach(function () {
    $this->formatter = new OutputFormatter();
    $this->command = Mockery::mock(Command::class);
});

test('it formats tables', function () {
    $this->command->shouldReceive('table')
        ->with(['ID', 'Name'], [[1, 'Server 1']])
        ->once();

    $this->formatter->table($this->command, ['ID', 'Name'], [[1, 'Server 1']]);
});

test('it formats tables as JSON', function () {
    $expectedJson = json_encode([
        ['id' => 1, 'name' => 'Server 1']
    ], JSON_PRETTY_PRINT);

    $this->command->shouldReceive('line')
        ->with($expectedJson)
        ->once();

    $this->formatter->table($this->command, ['ID', 'Name'], [[1, 'Server 1']], true);
});

test('it formats records', function () {
    $this->command->shouldReceive('line')
        ->with(Mockery::on(fn($line) => str_contains($line, 'Id:') && str_contains($line, '1')))
        ->once();
    
    $this->command->shouldReceive('line')
        ->with(Mockery::on(fn($line) => str_contains($line, 'Name:') && str_contains($line, 'Server 1')))
        ->once();

    $this->formatter->record($this->command, ['id' => 1, 'name' => 'Server 1']);
});

test('it formats records as JSON', function () {
    $data = ['id' => 1, 'name' => 'Server 1'];
    $expectedJson = json_encode($data, JSON_PRETTY_PRINT);

    $this->command->shouldReceive('line')
        ->with($expectedJson)
        ->once();

    $this->formatter->record($this->command, $data, true);
});

test('it formats errors', function () {
    $this->command->shouldReceive('newLine')->twice();
    $this->command->shouldReceive('line')
        ->with(Mockery::on(fn($line) => str_contains($line, '✗ Error:') && str_contains($line, 'Message')))
        ->once();
    $this->command->shouldReceive('line')
        ->with(Mockery::on(fn($line) => str_contains($line, '→ Hint:') && str_contains($line, 'Hint')))
        ->once();

    $this->formatter->error($this->command, 'Error', 'Message', 'Hint');
});

test('it formats success messages', function () {
    $this->command->shouldReceive('line')
        ->with("  <fg=green>✓</> Success")
        ->once();

    $this->formatter->success($this->command, 'Success');
});
