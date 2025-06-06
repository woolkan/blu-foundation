<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;

use Blu\Foundation\Core\ConfigManager;
use Predis\Client;

class LoginThrottler
{
    private const string LOGIN_ATTEMPT_KEY = 'login_attempts:';
    private const string LOGIN_BLOCK_KEY = 'login_blocks:';
    public function __construct(private Client $redis, private int $maxAttempts = 5, private int $blockTime = 300, private int $attemptTTL = 900)
    {
    }

    public static function fromConfig(Client $redis, ConfigManager $config): self
    {
        $maxAttempts = $config->has('security.loginThrottler.maxAttempts')
            ? (int)$config->get('security.loginThrottler.maxAttempts')
            : 5;

        $blockTime = $config->has('security.loginThrottler.blockTime')
            ? (int)$config->get('security.loginThrottler.blockTime')
            : 300;

        $attemptTTL = $config->has('security.loginThrottler.attemptTTL')
            ? (int)$config->get('security.loginThrottler.attemptTTL')
            : 900;

        return new self($redis, $maxAttempts, $blockTime, $attemptTTL);
    }

    public function registerFailedAttempt(string $ip): void
    {
        $attemptKey = self::LOGIN_ATTEMPT_KEY.$ip;
        $blockKey = self::LOGIN_BLOCK_KEY.$ip;

        $this->redis->incr($attemptKey);
        $this->redis->expire($attemptKey, $this->attemptTTL);

        if ((int)$this->redis->get($attemptKey) >= $this->maxAttempts) {
            $this->blockAttempt($ip);
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

    public function blockAttempt(string $ip): void
    {
        $attemptKey = self::LOGIN_ATTEMPT_KEY.$ip;
        $blockKey = self::LOGIN_BLOCK_KEY.$ip;

        $this->redis->setex($attemptKey,$this->attemptTTL, 0);
        $this->redis->setex($blockKey, $this->blockTime, 1);
    }
}