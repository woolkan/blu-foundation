# Blu Foundation

Install with composer:

```bash
composer require blu/foundation
```

## Container example

`LoginThrottler` can be configured via `ConfigManager`. Example definition for a PSR container:

```php
<?php
use Blu\Foundation\Core\ConfigManager;
use Blu\Foundation\Security\LoginThrottler;
use Predis\Client;
use Psr\Container\ContainerInterface;

return [
    LoginThrottler::class => static function(ContainerInterface $c): LoginThrottler {
        $config = $c->get(ConfigManager::class);
        $redis  = $c->get(Client::class);
        return LoginThrottler::fromConfig($redis, $config);
    },
];
```

Sample configuration structure:

```php
$config = new ConfigManager([
    'security' => [
        'loginThrottler' => [
            'maxAttempts' => 5,
            'blockTime'   => 300,
            'attemptTTL'  => 900,
        ],
    ],
]);
```
