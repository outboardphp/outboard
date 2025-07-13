<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\Resolver;
use Psr\Container\ContainerInterface;

class ExplicitResolver implements Resolver
{
    private array $definitions;
    private ContainerInterface $container;

    public function __construct(array $definitions, ContainerInterface $container)
    {
        $this->definitions = $definitions;
        $this->container = $container;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    public function resolve(string $id): ResolvedEntry
    {
        if (!array_key_exists($id, $this->definitions)) {
            throw new Exception\NotFoundException("No explicit definition for '$id'.");
        }
        $definition = $this->definitions[$id];
        $instance = is_callable($definition)
            ? $definition($this->container)
            : $definition;
        // If you have a Definition object, pass it; otherwise null
        $defObj = $definition instanceof Definition ? $definition : null;
        return new ResolvedEntry($id, $instance, $defObj);
    }
}
