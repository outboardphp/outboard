<?php

namespace Outboard\Di\Contracts;

use Outboard\Di\ResolvedEntry;

interface Resolver
{
    /**
     * Determine if this resolver can resolve the given id.
     */
    public function has(string $id): bool;

    /**
     * Resolve the given id.
     */
    public function resolve(string $id): ResolvedEntry;
}
