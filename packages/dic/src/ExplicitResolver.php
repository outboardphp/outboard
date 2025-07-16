<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class ExplicitResolver implements Resolver
{
    use Traits\NormalizesId;

    /**
     * @var array<string, ResolvedFactory>
     */
    protected array $definitionLookupCache = [];

    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        protected array $definitions = [],
    ) {}

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
                // or, the id is a regex that matches our target id
                || @\preg_match($defId, $id) === 1
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
        return new NotFoundException("No definition found for identifier: $id");
    }

    /**
     * @param string $id
     * @param Definition $definition
     * @param ContainerInterface $container
     * @return \Closure
     * @throws ContainerExceptionInterface
     */
    protected function makeClosure($id, $definition, $container)
    {
        // No reflection happening in this class, so there are things we just can't check

        /**
         * Closure level 1
         */
        if (\is_string($definition->substitute) && \class_exists($definition->substitute)) {
            // If the substitute is a class name, we'll instantiate that instead
            $id = $definition->substitute;

        } elseif (\is_object($definition->substitute)) {
            // If the substitute is an object, we assume it's a pre-existing instance
            $closure = static function () use ($definition) { return $definition->substitute; };

        } elseif (\is_callable($definition->substitute)) {
            // If the substitute is a callable, we call it to get the instance
            $closure = $definition->substitute;
        }

        /**
         * Closure level 2
         */
        if (!\is_object($definition->substitute) && $definition->withParams) {
            // We can still resolve class names to instances
            // NOTE: Cyclic dependencies are NOT supported in this resolver.
            $params = $this->getParams($definition->withParams, $container);
            $closure = static function () use ($id, $params) {
                // Instantiate the class, passing constructor arguments
                return new $id(...$params);
            };
        } elseif (!isset($closure) && $definition->singleton && $id === $container::class) {
            // If we're trying to instantiate the container itself, and the container is a singleton,
            // assume we want that rather than a new container just for the object graph
            $closure = static function () use ($container) { return $container; };

        } elseif (!isset($closure)) {
            // No constructor arguments, just make a basic closure
            $closure = static function () use ($id) { return new $id(); };
        }

        /**
         * Closure level 3
         *
         * If there are post-construct calls to make, do them after constructing the object
         */
        if ($definition->call) {
            $closure = static function () use ($closure, $definition, $container) {
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
        return $closure;
    }

    /**
     * Resolve container id strings to actual parameters.
     *
     * @param array $withParams The parameters to pass to the constructor.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @return array A closure that returns the parameters to pass to the constructor.
     * @throws ContainerExceptionInterface
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
