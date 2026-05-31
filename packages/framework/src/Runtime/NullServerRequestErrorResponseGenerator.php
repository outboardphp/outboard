<?php

declare(strict_types=1);

namespace Outboard\Framework\Runtime;

use Psr\Http\Message\ResponseInterface;

class NullServerRequestErrorResponseGenerator
{
    public function __invoke(\Throwable $error): ResponseInterface
    {
        throw new \LogicException('No server request error response generator is configured.');
    }
}
