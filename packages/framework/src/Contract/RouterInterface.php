<?php

declare(strict_types=1);

namespace Outboard\Framework\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Route the incoming request and produce a response.
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
}
