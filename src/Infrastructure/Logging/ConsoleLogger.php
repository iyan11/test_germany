<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

final class ConsoleLogger
{
    public function __construct(
        private readonly bool $verbose = false,
    ) {
    }

    public function info(string $message): void
    {
        $this->write('INFO', $message);
    }

    public function warning(string $message): void
    {
        $this->write('WARN', $message);
    }

    public function error(string $message): void
    {
        $this->write('ERROR', $message);
    }

    public function debug(string $message): void
    {
        if (!$this->verbose) {
            return;
        }

        $this->write('DEBUG', $message);
    }

    private function write(string $level, string $message): void
    {
        $timestamp = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        fwrite(STDOUT, sprintf('[%s] [%s] %s%s', $timestamp, $level, $message, PHP_EOL));
    }
}
