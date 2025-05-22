<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;

use Predis\Client;

class LoginThrottler
{
    private const string LOGIN_ATTEMPT_KEY = "login_attempts:";
    private const string LOGIN_BLOCK_KEY = "login_blocks:";
    public function __construct(private Client $redis, private int $maxAttempts = 5, private int $blockTime = 300, private int $attemptTTL = 900)
    {
    }

    public function registerFailedAttempt(string $ip): void
    {
        $attemptKey = self::LOGIN_ATTEMPT_KEY.$ip;
        $blockKey = self::LOGIN_BLOCK_KEY.$ip;

        $this->redis->incr($attemptKey);
        $this->redis->expire($attemptKey, $this->attemptTTL);

        if ((int)$this->redis->get($attemptKey) >= $this->maxAttempts) {
            $this->redis->setex($attemptKey,$this->attemptTTL, 0);
            $this->redis->setex($blockKey, $this->blockTime, 1);
        }
    }

    public function isBlocked(string $ip): bool
    {
        return (bool)$this->redis->exists(self::LOGIN_BLOCK_KEY.$ip);
    }

    public function resetAttempts(string $ip): void
    {
        $this->redis->del([self::LOGIN_ATTEMPT_KEY.$ip]);
    }

    public function resetBlocked(string $ip): void
    {
        $this->redis->del([self::LOGIN_BLOCK_KEY.$ip]);
    }

    public function getAttemptCount(string $ip): int
    {
        $attemptKey = self::LOGIN_ATTEMPT_KEY.$ip;
        return (int)($this->redis->get($attemptKey) ?? 0);
    }
}