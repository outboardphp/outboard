<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\ComposableContainer;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\ParameterReflection;

class Container implements ComposableContainer
{
    /**
     * @var array<string, mixed>
     * An associative array to hold the instances by their string id.
     */
    protected array $instances = [];

    /**
     * @var array<string, callable>
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
     * @param AbstractResolver[] $resolvers
     */
    public function __construct(
        protected array $resolvers,
    ) {}

    /**
     * @inheritDoc
     * @template T
     * @param class-string<T>|string $id Identifier of the entry to look for.
     * @throws ContainerException
     * @return T|mixed|null
     * @phpstan-ignore method.templateTypeNotInParameter
     */
    public function get(string $id)
    {
        // Cached shared instance
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        // Cached non-shared instance
        if (isset($this->factories[$id])) {
            return $this->factories[$id]();
        }

        // First-time resolution
        return $this->resolve($id);
    }

    public function has(string $id): bool
    {
        return \array_any($this->resolvers, fn($resolver) => $resolver->has($id));
    }

    /**
     * @param array<int|string, mixed> $args Supports named or positional parameters by array key
     * @throws ContainerException if a parameter's value cannot be determined
     */
    public function call(callable $callable, array $args = []): mixed
    {
        // Reflect on callable params
        $reflection = CallableReflection::fromCallable($callable);
        $params = $reflection->getParameters();
        foreach ($params as $position => &$param) {
            // First, complete the arg from the supplied array preferentially
            $paramName = $param->getName();
            if (isset($args[$paramName]) || isset($args[$position])) {
                $param = $args[$paramName] ?? $args[$position];
                continue;
            }

            // If we made it here, we need to resolve from the container
            // But first, we have to make sure we're dealing with a class name
            $paramClasses = \array_filter($param->getTypes(), static fn($type) => $type->isClassName());

            if (empty($paramClasses)) {
                if (!$param->isOptional()) {
                    throw new ContainerException("Required parameter '$paramName' must be manually supplied or typed with a class name.");
                }
                $param = null;
                continue;
            }

            $resolved = false;
            foreach ($paramClasses as $type) {
                // Go with the first class name we can use
                try {
                    $param = $this->resolve($type->getType());
                    $resolved = true;
                    break;
                } catch (NotFoundException) {
                    continue;
                }
            }
            if (!$resolved) {
                /** @phpstan-var ParameterReflection $param */
                if (!$param->isOptional()) {
                    throw new ContainerException("Unable to resolve parameter '$paramName'.");
                }
                $param = null;
            }
        }

        return $callable(...$params);
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

    /**
     * @throws ContainerException
     */
    protected function resolve(string $id): mixed
    {
        // Find a resolver that can resolve this id
        $resolver = \array_find($this->resolvers, fn($resolver) => $resolver->has($id));
        if ($resolver === null) {
            throw new NotFoundException("No entry was found for '$id'.");
        }

        $resolution = $resolver->resolve($id, $this->parent ?? $this);
        if (!$resolution->factory) {
            throw new ContainerException('Should not happen');
        }
        if ($resolution->definition->shared) {
            $this->instances[$id] = ($resolution->factory)();
            return $this->instances[$id];
        }
        $this->factories[$id] = $resolution->factory;
        return $this->factories[$id]();
    }
}
