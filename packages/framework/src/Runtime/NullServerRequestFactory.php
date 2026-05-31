<?php

namespace Outboard\Framework\Runtime;

use Psr\Http\Message\ServerRequestInterface;

class NullServerRequestFactory
{
    public function __invoke(): ServerRequestInterface
    {
        throw new \LogicException('No server request factory is configured.');
    }
}
