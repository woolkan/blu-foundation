<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;
use Blu\Foundation\Security\Exception\HoneypotException;

class SimpleHoneypotService
{
    public function __construct()
    {
    }

    public function validate(array $data): void
    {
        $honeypot = trim($data['your_name'] ?? '');
        if (!empty($honeypot))
            throw new HoneypotException(message: "Witaj bocie!");
    }
}