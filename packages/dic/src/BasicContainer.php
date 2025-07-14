<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\ComposableContainer;
use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\Traits\ContainerCommonArrayAccess;
use Outboard\Di\Traits\RespectfulContainer;

class BasicContainer implements ComposableContainer, \ArrayAccess
{
    use RespectfulContainer;
    use ContainerCommonArrayAccess;

    /**
     * @var array<string, mixed> $instances
     * An associative array to hold the instances by their string id.
     */
    protected array $instances = [];

    /**
     * @var Resolver[]
     */
    protected array $resolvers = [];

    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @inheritDoc
     * @template T
     * @param string|class-string<T> $id Identifier of the entry to look for.
     * @return T|mixed|null
     * @throws ContainerException
     */
    public function get(string $id)
    {
        /** @var Resolver $found */
        $found = \array_find($this->resolvers, fn($resolver) => $resolver->has($id));
        if ($found !== null) {
            return $found->resolve($id)->instance;
        }
        throw new NotFoundException("No entry was found for '$id'.");
    }

    public function has(string $id): bool
    {
        /** @var Resolver $resolver */
        return \array_any($this->resolvers, fn($resolver) => $resolver->has($id));
    }
}
