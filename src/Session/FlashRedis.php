<?php
declare(strict_types=1);

namespace Blu\Foundation\Session;

use Predis\Client;

readonly class FlashRedis
{
    public function __construct(private Client $redisClient, private string $sessionId, private string $prefix = "flash:")
    {
    }

    public function set(string $key, string $message, int $ttl = 300): void
    {
        $this->redisClient->setex($this->prefix . $this->sessionId . ':' . $key, $ttl, $message);
    }

    public function get(string $key): ?string
    {
        $fullKey = $this->prefix . $this->sessionId . ':' . $key;
        $message = $this->redisClient->get($fullKey);
        $this->redisClient->del($fullKey); // odczyt jednokrotny
        return $message;
    }
}