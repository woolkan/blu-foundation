<?php
declare(strict_types=1);

namespace Blu\Foundation\Middleware;

use Blu\Foundation\Security\AccessControl;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AccessControl $accessControl,
        private string $permission,
        private callable $roleResolver
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $role = ($this->roleResolver)($request);
        if (!$this->accessControl->isAllowed($role, $this->permission)) {
            return new Response(403);
        }
        return $handler->handle($request);
    }
}
