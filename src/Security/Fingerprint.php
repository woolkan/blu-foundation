<?php
declare(strict_types=1);

namespace Blu\Foundation\Security;

use Psr\Http\Message\ServerRequestInterface;

class Fingerprint
{
    public function __construct(private string $secret)
    {
    }

    /**
     * Tworzy fingerprint dla danego żądania
     */
    public function generate(ServerRequestInterface $request): string
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '';
        $ua = $request->getServerParams()['HTTP_USER_AGENT'] ?? '';

        return hash('sha256', $ip . $ua . $this->secret);
    }

    /**
     * Sprawdza czy fingerprint zgadza się z oczekiwanym
     */
    public function isValid(ServerRequestInterface $request, string $stored): bool
    {
        $current = $this->generate($request);
        return hash_equals($stored, $current); // zapobiega timing attacks
    }
}