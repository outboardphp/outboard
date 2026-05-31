<?php

declare(strict_types=1);

namespace Outboard\Framework\Runtime;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class DefaultServerRequestErrorResponseGenerator
{
    public function __invoke(Throwable $error): ResponseInterface
    {
        return new TextResponse('Internal Server Error', 500);
    }
}
