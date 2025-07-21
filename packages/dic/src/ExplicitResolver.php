<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\ResolvedFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class ExplicitResolver implements Resolver
{
    use Traits\NormalizesId;
    use Traits\TestsRegexSilently;

    /** @var array<string, ResolvedFactory> */
    protected array $definitionLookupCache = [];

    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        protected array $definitions = [],
    ) {
        // Normalize the definitions to ensure they are in a consistent format
        foreach ($this->definitions as $id => $definition) {
            $this->definitions[static::normalizeId($id)] = $definition;
        }
    }

    /**
     * Checks if this resolver is capable of resolving the given identifier.
     */
    public function has(string $id): bool
    {
        if (isset($this->definitionLookupCache[$id])) {
            // We already know we have a definition for this id, so save a few cycles
            return true;
        }
        try {
            // Find the right one and cache it
            $this->definitionLookupCache[$id] = $this->find($id);
            return true;
        } catch (NotFoundException $e) {
            // We didn't find a definition, nothing was cached,
            // and we're explicit, so that means it's a no
            return false;
        }
    }

    /**
     * Resolve an identifier to a ResolvedFactory by applying the appropriate Definition
     * and deferring to a Container as needed to resolve recursive dependencies.
     *
     * @throws ContainerExceptionInterface
     */
    public function resolve(string $id, ContainerInterface $container): ResolvedFactory
    {
        // prime the cache or error out
        $this->has($id) || throw $this->notFound($id);

        $rf = $this->definitionLookupCache[$id];
        if ($rf->definition === null) {
            // This should never happen, but if it does, we throw an error
            throw new NotFoundException('Should not happen');
        }

        $rf->factory = $this->makeClosure($id, $rf->definition, $container);
        return $rf;
    }

    /**
     * Find the definition for the given identifier or throw an exception if not found.
     * Returns an incomplete ResolvedFactory object containing only the matching definition.
     *
     * @param string $id
     * @throws NotFoundException
     * @return ResolvedFactory
     */
    protected function find($id)
    {
        // First, check for an exact match
        $normalName = static::normalizeId($id);
        if (isset($this->definitions[$normalName])) {
            return new ResolvedFactory(
                definitionId: $normalName,
                definition: $this->definitions[$normalName],
            );
        }

        // Next, look for a non-exact match
        foreach ($this->definitions as $defId => $definition) {
            if ($defId === '*') {
                // Skip the catch-all definition for now, if it exists
                continue;
            }
            if (
                // The current definition can apply to subclasses, and its id is a parent of our target class
                ($definition->strict === false && \is_subclass_of($id, $defId))
                // or the id is a regex that matches our target id
                || static::testRegexSilently($defId, $id) === 1
            ) {
                return new ResolvedFactory(
                    definitionId: $defId,
                    definition: $definition,
                );
            }
        }
        // If we get here, return the catch-all definition if it exists
        if (isset($this->definitions['*'])) {
            return new ResolvedFactory(
                definitionId: '*',
                definition: $this->definitions['*'],
            );
        }
        // Since we're an explicit container, this is now an error case
        throw $this->notFound($id);
    }

    /**
     * @param string $id
     * @return NotFoundException
     */
    protected function notFound($id)
    {
        return new NotFoundException("No definition found for identifier: {$id}");
    }

    /**
     * @param string $id
     * @param Definition $definition
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @return \Closure
     */
    protected function makeClosure($id, $definition, $container)
    {
        // No reflection happening in this class, so there are things we just won't be able to check

        // Intent to substitute with the result of a callable short-circuits the remaining logic
        // Callable supports params and post-call
        if (\is_callable($definition->substitute)) {
            $withParams = $this->addParams(($definition->substitute)(...), $definition, $container);
            return $this->addPostCall($withParams, $definition, $container);
        }

        // Intent to substitute an existing object short-circuits the remaining logic
        // Object supports post-call only
        if (\is_object($definition->substitute)) {
            return $this->addPostCall(
                static function () use ($definition) { return $definition->substitute; },
                $definition,
                $container,
            );
        }

        // Now, if the substitute is a string, we expect it to be a class name or an id of another definition.
        if (\is_string($definition->substitute)) {
            if ($container->has($definition->substitute)) {
                return $this->addPostCall(
                    static function () use ($definition, $container) {
                        // Get the instance from the container
                        return $container->get($definition->substitute);
                    },
                    $definition,
                    $container,
                );
            }
            if (!\class_exists($definition->substitute)) {
                throw new NotFoundException(
                    "Substitute '{$definition->substitute}' not found for definition '{$id}'",
                );
            }
            $id = $definition->substitute;
        }

        // At this point, expect the $id to be a class name.

        // If the intent is to instantiate the container itself, and the container is a singleton,
        // assume we want the existing one rather than a new container just for the object graph,
        // therefore only post-call is supported
        if ($definition->singleton && $id === $container::class) {
            return $this->addPostCall(
                static function () use ($container) { return $container; },
                $definition,
                $container,
            );
        }

        $withParams = $this->addParams(
            static function (...$params) use ($id) {
                // Instantiate the class, passing constructor arguments
                return new $id(...$params);
            },
            $definition,
            $container,
        );
        return $this->addPostCall($withParams, $definition, $container);
    }

    /**
     * If the definition has parameters, wrap the closure to pass them.
     * This allows for dependency injection of parameters into the closure.
     *
     * @param \Closure $closure The closure that creates the object.
     * @param Definition $definition The definition containing parameters.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @throws ContainerExceptionInterface from getParams()
     * @return \Closure A closure that returns the object with parameters injected.
     */
    protected function addParams($closure, $definition, $container)
    {
        if (!$definition->withParams) {
            // No parameters to pass, return the closure as is
            return $closure;
        }

        // We can still resolve class names to instances
        // NOTE: Cyclic dependencies are NOT supported in this resolver.
        $params = $this->getParams($definition->withParams, $container);
        return static function () use ($closure, $params) {
            // Call the closure, passing arguments
            return $closure(...$params);
        };
    }

    /**
     * If the definition has a post-call, wrap the closure to call it after instantiation.
     *
     * @param \Closure $closure The closure that creates the object.
     * @param Definition $definition The definition containing the post-call.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @return \Closure A closure that returns the object after calling the post-call.
     */
    protected function addPostCall($closure, $definition, $container)
    {
        if (!$definition->call) {
            // No post-call, return the closure as is
            return $closure;
        }

        return static function () use ($closure, $definition, $container) {
            // Construct the object using the original closure
            $object = $closure();

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
