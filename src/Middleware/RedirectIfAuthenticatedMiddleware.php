<?php
declare(strict_types=1);

namespace Blu\Middleware;

use Blu\Service\Authorization\AuthService;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

final readonly class RedirectIfAuthenticatedMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $auth,
        private string      $dashboardRoute = 'v1.auth.dashboard'   // nazwa trasy, nie URL!
    )
    {
    }

    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {

        if ($this->auth->isLoggedIn($request)) {
            $url = RouteContext::fromRequest($request)
                ->getRouteParser()
                ->urlFor($this->dashboardRoute);

            return (new Response())
                ->withHeader('Location', $url)
                ->withStatus(302);
        }

        // użytkownik NIE jest zalogowany → wyświetlamy formularz
        return $handler->handle($request);
    }
}