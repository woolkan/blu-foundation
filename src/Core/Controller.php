<?php
declare(strict_types=1);

namespace Blu\Foundation\Core;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

final readonly class Controller
{
    public function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    public function htmlResponse(Request $request, Response $response, string $template, array $data = [], int $status = 200): Response
    {
        // Assuming $this->twig is an instance of Twig\Environment
        $view = Twig::fromRequest($request);
        return $view->render($response, $template, $data)
            ->withStatus($status)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

}