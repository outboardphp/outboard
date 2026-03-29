<?php

namespace Outboard\Di;

use Outboard\Di\Contract\CacheInterface;
use Outboard\Di\Contract\ComposableContainer;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\ValueObject\ResolvedFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Technically\CallableReflection\CallableReflection;
use Technically\CallableReflection\Parameters\ParameterReflection;

class Container implements ComposableContainer
{
    /**
     * Manages caching of shared instances and non-shared factories.
     */
    protected CacheInterface $cache;

    /**
     * Holds a ref to the parent container if this is a child, for
     * dependency resolution purposes.
     */
    protected ?ContainerInterface $parent;

    /**
     * @param Resolver[] $resolvers
     * @param CacheInterface|null $cache Optional cache instance (creates default if not provided)
     */
    public function __construct(
        protected array $resolvers,
        ?CacheInterface $cache = null,
    ) {
        $this->cache = $cache ?? new InstanceCache();
    }

    /**
     * @inheritDoc
     * @template T of object
     * @param class-string<T>|string $id Identifier of the entry to look for.
     * @return ($id is class-string<T> ? T : mixed)
     * @throws \ReflectionException
     */
    public function get(string $id)
    {
        // Cached shared instance
        if ($this->cache->hasShared($id)) {
            return $this->cache->getShared($id);
        }
        // Cached non-shared instance
        if ($this->cache->hasFactory($id)) {
            return $this->cache->getFactory($id)();
        }

        // First-time resolution
        $resolution = $this->resolve($id);
        $instance = ($resolution->factory)();
        $this->cacheResolution($id, $resolution, $instance);
        return $instance;
    }

    /**
     * Always gets a fresh instance of a service, but uses the cached factory if possible.
     *
     * @param string $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function make(string $id)
    {
        if ($this->cache->hasFactory($id)) {
            return $this->cache->getFactory($id)();
        }

        // First-time resolution, but don't cache anything
        $resolution = $this->resolve($id);
        return ($resolution->factory)();
    }

    public function has(string $id): bool
    {
        return \array_any($this->resolvers, fn($resolver) => $resolver->has($id));
    }

    /**
     * @param array<int|string, mixed> $args Supports named or positional parameters by array key
     * @throws ContainerExceptionInterface if a parameter's value cannot be determined
     * @throws \ReflectionException
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
                // Use the default value (will be null if no default was specified)
                $param = $param->getDefaultValue();
                continue;
            }

            $resolved = false;
            foreach ($paramClasses as $type) {
                // Go with the first class name we can use
                try {
                    $param = $this->get($type->getType());
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
                // Use the default value (will be null if no default was specified)
                $param = $param->getDefaultValue();
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
     * @throws ContainerExceptionInterface|\ReflectionException
     */
    protected function resolve(string $id): ResolvedFactory
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
        return $resolution;
    }

    protected function cacheResolution(string $id, ResolvedFactory $resolution, mixed $instance): void
    {
        if ($resolution->definition->shared) {
            $this->cache->setShared($id, $instance);
        }
        $factory = $resolution->factory;
        $this->cache->setFactory($id, $factory);
    }
}
