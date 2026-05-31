<?php

declare(strict_types=1);

namespace Outboard\Framework\Runtime;

use Outboard\Framework\Contract\ResponseEmitter;

class NoopResponseEmitter implements ResponseEmitter
{
    public function emit(mixed $response): void
    {
        if ($response === null) {
            return;
        }

        echo (string) $response;
    }
}
