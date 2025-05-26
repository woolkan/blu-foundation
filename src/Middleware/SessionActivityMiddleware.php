<?php
declare(strict_types=1);

namespace Blu\Foundation\Middleware;

use Blu\Foundation\Session\FlashRedis;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class SessionActivityMiddleware implements MiddlewareInterface
{
    public function __construct(private int $timeout, private FlashRedis $flash, private string $configRedirectUri){}
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor($this->configRedirectUri);

        if (!isset($_SESSION['user'])) {
            $this->flash->set('auth_error', 'Dostęp wymaga zalogowania.');
            return (new Response())->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        $now = time();
        $last = $_SESSION['last_activity'] ?? $now;

        if (($now - $last) > $this->timeout) {
            session_destroy();
            $this->flash->set('auth_error', 'Twoja sesja wygasła z powodu braku aktywności.');
            return (new Response())->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        $_SESSION['last_activity'] = $now;
        return $handler->handle($request);
    }
}