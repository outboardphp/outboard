<?php

declare(strict_types=1);

namespace Outboard\Framework\Contract;

interface ResponseEmitter
{
    /**
     * Emit the response-like value to the current output target.
     */
    public function emit(mixed $response): void;
}
