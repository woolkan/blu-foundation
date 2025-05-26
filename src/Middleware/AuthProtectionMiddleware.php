<?php
declare(strict_types=1);

namespace Blu\Foundation\Middleware;

use Blu\Foundation\Security\Fingerprint;
use Blu\Foundation\Security\LoginThrottler;
use Blu\Foundation\Session\FlashRedis;
use Blu\Foundation\Session\FlashRedisEnum;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

readonly class AuthProtectionMiddleware implements MiddlewareInterface
{
    public function __construct(private LoginThrottler $throttler, private Fingerprint $fingerprint, private FlashRedis $flash, private string $configRedirectUri){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '';
        $storedFingerprint = $_SESSION['fingerprint'] ?? null;

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor($this->configRedirectUri);
        if ($this->throttler->isBlocked($ip)) {
            $this->flash->set(FlashRedisEnum::Flash_Msq_Key_Auth_Error->value, 'Dostęp zablokowany z powodu zbyt wielu prób logowania.');
            return new Response()->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        if (!$storedFingerprint || !$this->fingerprint->isValid($request, $storedFingerprint)) {
            session_destroy();
            $this->flash->set(FlashRedisEnum::Flash_Msq_Key_Auth_Error->value, 'Twoja sesja została przerwana z powodu błędnego fingerprinta.');
            return new Response()->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        return $handler->handle($request);
    }
}