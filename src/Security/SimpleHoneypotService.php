<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;
use Blu\Foundation\Security\Exception\SecurityException;

class SimpleHoneypotService
{
    public function __construct()
    {
    }

    public function validate(array $data): void
    {
        $honeypot = trim($data['your_name'] ?? '');
        if (!empty($honeypot))
            throw new SecurityException(message: "Witaj bocie!");
    }
}