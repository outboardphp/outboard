<?php

namespace Outboard\Di;

use Outboard\Di\Matching\DefinitionMatcher;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\ResolvedFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractResolver
{
    /** @var array<string, ?ResolvedFactory> */
    protected array $definitionLookupCache = [];

    /**
     * @param callable $callable The callable to autowire.
     * @param Definition $definition The definition containing parameters.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @throws ContainerExceptionInterface
     * @return callable
     */
    abstract protected function callableAddParams($callable, $definition, $container);

    /**
     * @param \Closure $closure The closure we're constructing.
     * @param class-string $id The class name to autowire.
     * @param Definition $definition The definition containing parameters.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @return callable
     */
    abstract protected function constructorAddParams($closure, $id, $definition, $container);

    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        protected array $definitions = [],
        protected DefinitionIdNormalizer $definitionIdNormalizer = new DefinitionIdNormalizer(),
        protected DefinitionMatcher $definitionMatcher = new DefinitionMatcher(),
    ) {
        // Normalize the definitions to ensure they are in a consistent format
        $normalized = [];
        foreach ($this->definitions as $id => $definition) {
            $normalized[$this->definitionIdNormalizer->normalizeDefinitionId($id)] = $definition;
        }
        $this->definitions = $normalized;
    }

    /**
     * Determine if this resolver can resolve the given id.
     */
    public function has(string $id): bool
    {
        if (isset($this->definitionLookupCache[$id])) {
            // We already know we have a definition for this id, so save a few cycles
            return true;
        }
        // Look for the right one
        $def = $this->find($id);
        if ($def !== null) {
            // We found one, so cache it
            $this->definitionLookupCache[$id] = $def;
            return true;
        }
        // We didn't find a definition, nothing was cached
        return false;
    }

    /**
     * Find the definition for the given identifier.
     *
     * @param string $id
     * @return ?ResolvedFactory incomplete object containing only the matching definition, or null if not found
     */
    protected function find($id)
    {
        return $this->definitionMatcher->match($id, $this->definitions);
    }

    /**
     * Resolve an identifier to a ResolvedFactory by applying the appropriate Definition
     * and deferring to a Container as needed to resolve recursive dependencies.
     *
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function resolve(string $id, ContainerInterface $container): ResolvedFactory
    {
        // (prime the cache/class exists) or error out
        $this->has($id) or throw new NotFoundException("No definition found for identifier: {$id}");

        // Either load the definition we found or create a new one uncached
        $rf = $this->definitionLookupCache[$id]
            ?? new ResolvedFactory(null, $id, new Definition());

        $rf->factory = $this->makeClosure($id, $rf->definition, $container);

        return $rf;
    }

    /**
     * @param string $id
     * @param Definition $definition
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws \ReflectionException
     * @return \Closure
     */
    protected function makeClosure($id, $definition, $container)
    {
        $closure = null;

        // Intent to substitute with the result of a callable short-circuits the remaining logic
        // Callable supports params and post-call
        if (\is_callable($definition->substitute)) {
            $closure = $this->callableAddParams(($definition->substitute)(...), $definition, $container);
        } elseif (\is_object($definition->substitute)) {
            // Intent to substitute an existing object short-circuits the remaining logic
            // Object supports post-call only
            $closure = static function () use ($definition) { return $definition->substitute; };
        } else {
            $targetId = $id;

            // Now, if the substitute is a string, we expect it to be a class name or an id of another definition.
            if (\is_string($definition->substitute)) {
                [$closure, $targetId] = $this->resolveStringSubstitute(
                    $id,
                    $definition->substitute,
                    $container,
                );
            }

            // At this point, expect the $targetId to be a class name.
            /** @var class-string $targetId */

            if ($closure === null) {
                $closure = $this->buildInstantiationClosure($targetId, $definition, $container);
            }
        }

        return $this->addPostCall($closure, $definition, $container);
    }

    /**
     * Resolve a string substitute to either a container-backed closure or a class-string target id.
     *
     * @param string $definitionId
     * @param string $substitute
     * @param ContainerInterface $container
     * @throws NotFoundException
     * @return array{0: ?\Closure, 1: string}
     */
    protected function resolveStringSubstitute($definitionId, $substitute, $container)
    {
        if ($container->has($substitute)) {
            return [
                static function () use ($substitute, $container) {
                    // Get the instance from the container
                    return $container->get($substitute);
                },
                $definitionId,
            ];
        }

        if (!\class_exists($substitute)) {
            throw new NotFoundException(
                "Substitute '{$substitute}' not found for definition '{$definitionId}'",
            );
        }

        return [null, $substitute];
    }

    /**
     * Build the base instantiation closure for a resolved class-string target.
     *
     * @param class-string $targetId
     * @param Definition $definition
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     * @return callable
     */
    protected function buildInstantiationClosure($targetId, $definition, $container)
    {
        // If the intent is to instantiate the container itself, and the container is shared,
        // assume we want the existing one rather than a new container just for the object graph,
        // therefore only post-call is supported
        if ($definition->shared && $targetId === $container::class) {
            return static function () use ($container) { return $container; };
        }

        $factory = static fn(...$params) => new $targetId(...$params);
        return $this->constructorAddParams($factory, $targetId, $definition, $container);
    }

    /**
     * If the definition has parameters, wrap the closure to pass them.
     * This allows for dependency injection of parameters into the closure.
     *
     * @param callable $callable The closure that creates the object.
     * @param Definition $definition The definition containing parameters.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @throws ContainerExceptionInterface from getParams()
     * @return \Closure A closure that returns the object with parameters injected.
     */
    protected function addParams($callable, $definition, $container)
    {
        if (!$definition->withParams) {
            // No parameters to pass, return the closure as is
            return $callable instanceof \Closure ? $callable : $callable(...);
        }

        // We can still resolve class names to instances
        // NOTE: Cyclic dependencies are NOT supported in this resolver.
        $params = $this->getParams($definition->withParams, $container);
        return static function () use ($callable, $params) {
            // Call the closure, passing arguments
            return $callable(...$params);
        };
    }

    /**
     * If the definition has a post-call, wrap the closure to call it after instantiation.
     *
     * @param callable $callable The closure that creates the object.
     * @param Definition $definition The definition containing the post-call.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @return \Closure A closure that returns the object after calling the post-call.
     */
    protected function addPostCall($callable, $definition, $container)
    {
        if (!$definition->call) {
            // No post-call, return the closure as is
            return $callable instanceof \Closure ? $callable : $callable(...);
        }

        return static function () use ($callable, $definition, $container) {
            // Construct the object using the original closure
            $object = $callable();

            // Call the closure and pass in our new object as well as the container
            $return = ($definition->call)($object, $container);
            // If the call returns something, we assume it's a decorator or reducer - replace the original object
            if ($return !== null) {
                $object = $return;
            }

            return $object;
        };
    }

    /**
     * Resolve container id strings to actual parameters.
     *
     * @param mixed[] $withParams The parameters to pass to the constructor
     * @param ContainerInterface $container The container to resolve dependencies from
     * @throws ContainerExceptionInterface
     * @return mixed[] The original array with container ids resolved to actual instances
     */
    protected function getParams($withParams, $container)
    {
        foreach ($withParams as &$value) {
            if (\is_string($value) && $container->has($value)) {
                $value = $container->get($value);
            }
        }
        return $withParams;
    }
}
