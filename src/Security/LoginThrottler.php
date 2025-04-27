<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;

use Predis\Client;

class LoginThrottler
{
    public function __construct(private Client $redis, private int $maxAttempts = 5, private int $blockTime = 300, private int $attemptTTL = 900)
    {
    }

    public function registerFailedAttempt(string $ip): void
    {
        $attemptKey = "login_attempts:{$ip}";
        $blockKey = "login_blocked:{$ip}";

        $this->redis->incr($attemptKey);
        $this->redis->expire($attemptKey, $this->attemptTTL);

        if ((int)$this->redis->get($attemptKey) >= $this->maxAttempts) {
            $this->redis->setex($attemptKey,$this->attemptTTL, 0);
            $this->redis->setex($blockKey, $this->blockTime, 1);
        }
    }

    public function isBlocked(string $ip): bool
    {
        return (bool)$this->redis->exists("login_blocked:{$ip}");
    }

    public function resetAttempts(string $ip): void
    {
        $this->redis->del(["login_attempts:{$ip}"]);
    }

    public function resetBlocked(string $ip): void
    {
        $this->redis->del(["login_blocked:{$ip}"]);
    }

    public function getAttemptCount(string $ip): int
    {
        $attemptKey = "login_attempts:{$ip}";
        return (int)($this->redis->get($attemptKey) ?? 0);
    }
}