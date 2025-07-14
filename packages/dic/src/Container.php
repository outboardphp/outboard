<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\ComposableContainer;
use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class Container implements ComposableContainer
{
    /**
     * @var array<string, mixed> $instances
     * An associative array to hold the instances by their string id.
     */
    protected array $instances = [];

    /**
     * @var array<string, callable> $factories
     * An associative array to hold the factories by their string id.
     * The callable should return an instance of the requested type.
     */
    protected array $factories = [];

    /**
     * Holds a ref to the parent container if this is a child, for
     * dependency resolution purposes.
     */
    protected ?ContainerInterface $parent;

    /**
     * @param Resolver[] $resolvers
     */
    public function __construct(protected array $resolvers) {}

    /**
     * @inheritDoc
     * @template T
     * @param class-string<T>|string $id Identifier of the entry to look for.
     * @return T|mixed|null
     * @throws ContainerException
     * @phpstan-ignore method.templateTypeNotInParameter
     */
    public function get(string $id)
    {
        // Repeat singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        // Repeat non-singleton
        if (isset($this->factories[$id])) {
            return $this->factories[$id]();
        }

        // First-time resolution
        return $this->resolve($id);
    }

    /**
     * @throws ContainerException
     */
    protected function resolve(string $id): mixed
    {
        $found = \array_find($this->resolvers, fn($resolver) => $resolver->has($id));
        if (!($found instanceof Resolver)) {
            throw new NotFoundException("No entry was found for '$id'.");
        }

        $factory = $found->resolve($id, $this->parent ?? $this);
        if ($factory->matchedDefinition->singleton) {
            $this->instances[$id] = ($factory->factory)();
            return $this->instances[$id];
        }
        $this->factories[$id] = $factory->factory;
        return $this->factories[$id]();
    }

    public function has(string $id): bool
    {
        return \array_any($this->resolvers, fn($resolver) => $resolver->has($id));
    }

    /**
     * @throws ContainerException
     */
    public function setParent(ContainerInterface $container): void
    {
        if (isset($this->parent)) {
            throw new ContainerException('Parent container is already set.');
        }
        $this->parent = $container;
    }
}
