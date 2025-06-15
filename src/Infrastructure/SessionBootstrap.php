<?php
declare(strict_types=1);

namespace Blu\Foundation\Infrastructure;

final readonly class SessionBootstrap
{
    public function __construct(
        private string $scheme,
        private string $host,
        private int    $port,
        private string $password,
        private int    $database      = 2,
        private int    $timeout       = 1,
        private int    $gcMaxLifetime = 900
    ) {}

    /** Uruchamia konfigurację i session_start(); klasa jest „wywoływalna”. */
    public function __invoke(): void
    {
        ini_set(
            'session.save_path',
            sprintf(
                '%s://%s:%d?auth=%s&database=%d&timeout=%d',
                $this->scheme,
                $this->host,
                $this->port,
                rawurlencode($this->password),
                $this->database,
                $this->timeout
            )
        );
        ini_set('session.save_handler',   'redis');
        ini_set('session.gc_maxlifetime', (string) $this->gcMaxLifetime);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}