<?php

namespace Outboard\Di\Contracts;

use Outboard\Di\ValueObjects\ResolvedFactory;
use Psr\Container\ContainerInterface;

interface Resolver
{
    /**
     * Determine if this resolver can resolve the given id.
     */
    public function has(string $id): bool;

    /**
     * Resolve the given id.
     */
    public function resolve(string $id, ContainerInterface $container): ResolvedFactory;
}
