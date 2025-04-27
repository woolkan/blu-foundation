<?php
declare(strict_types=1);

namespace Blu\Foundation\Middleware;

use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Csrf\Guard;
use Slim\Views\Twig;

class CsrfViewMiddleware
{
    public function __construct(private Twig $twig, private Guard $csrf)
    {
    }

    public function __invoke(Request $request, Handler $handler): ResponseInterface
    {
        $this->twig->getEnvironment()->addGlobal('csrf', [
            'keys' => [
                'name' => $this->csrf->getTokenNameKey(),
                'value' => $this->csrf->getTokenValueKey(),
            ],
            'name'  => $this->csrf->getTokenName(),
            'value' => $this->csrf->getTokenValue(),
        ]);

        return $handler->handle($request);
    }
}