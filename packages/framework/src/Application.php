<?php

declare(strict_types=1);

namespace Outboard\Framework;

use Outboard\Framework\Contract\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class Application implements RequestHandlerInterface
{
    public function __construct(
        public RouterInterface $router,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->router->dispatch($request);
    }
}
