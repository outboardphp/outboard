<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\ComposableContainer;
use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\Traits\RespectfulContainer;

class BasicContainer implements ComposableContainer
{
    use RespectfulContainer;

    /**
     * @var array<string, mixed> $instances
     * An associative array to hold the instances by their string id.
     */
    protected array $instances = [];

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
        $found = \array_find($this->resolvers, fn($resolver) => $resolver->has($id));
        if ($found instanceof Resolver) {
            return $found->resolve($id)->instance;
        }
        throw new NotFoundException("No entry was found for '$id'.");
    }

    public function has(string $id): bool
    {
        return \array_any($this->resolvers, fn($resolver) => $resolver->has($id));
    }
}
