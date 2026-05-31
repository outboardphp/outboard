<?php

declare(strict_types=1);

namespace Outboard\Framework\Runtime;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Outboard\Framework\Contract\RouterInterface;

class NullRouter implements RouterInterface
{
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        throw new \LogicException('No router implementation is configured.');
    }
}
