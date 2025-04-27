<?php
declare(strict_types=1);

namespace Blu\Foundation\Core;

use InvalidArgumentException;

class ConfigManager
{
    public function __construct(private readonly array $config)
    {}

    /**
     * Retrieve a configuration value by key.
     * Supports dot notation for nested arrays (e.g., "database.host").
     *
     * @param string $key
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $subKey) {
            if (!array_key_exists($subKey, $value)) {
                throw new InvalidArgumentException(sprintf('Configuration key "%s" does not exist.', $key));
            }
            $value = $value[$subKey];
        }

        return $value;
    }

    /**
     * Check if a configuration key exists.
     * Supports dot notation for nested arrays (e.g., "database.host").
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $subKey) {
            if (!array_key_exists($subKey, $value)) {
                return false;
            }
            $value = $value[$subKey];
        }

        return true;
    }
}