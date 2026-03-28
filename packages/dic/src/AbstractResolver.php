<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\SubstitutionResolverInterface;
use Outboard\Di\Enums\SubstitutionMode;
use Outboard\Di\Matching\DefinitionMatcher;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\Support\PostCallDecorator;
use Outboard\Di\Substitution\SubstitutionResolverChain;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\ResolvedFactory;
use Outboard\Di\ValueObjects\SubstitutionResolution;
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
        protected SubstitutionResolverInterface $substitutionResolver = new SubstitutionResolverChain(),
        protected PostCallDecorator $postCallDecorator = new PostCallDecorator(),
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
        $substitution = $this->substitutionResolver->resolve($id, $definition, $container);

        $closure = match ($substitution->mode) {
            SubstitutionMode::Callable
                => $this->resolveCallableSubstitution($substitution, $definition, $container),
            SubstitutionMode::Raw
                => $this->resolveRawSubstitution($substitution),
            SubstitutionMode::Constructor
                => $this->resolveConstructorSubstitution($substitution, $definition, $container),
            default
                => throw new NotFoundException('Unknown substitution mode encountered.'),
        };

        return $this->postCallDecorator->decorate($closure, $definition, $container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @return callable
     */
    protected function resolveCallableSubstitution(SubstitutionResolution $substitution, $definition, $container)
    {
        if ($substitution->factory === null) {
            throw new NotFoundException('Missing callable factory for substitution.');
        }

        return $this->callableAddParams($substitution->factory, $definition, $container);
    }

    /**
     * @return \Closure
     * @throws NotFoundException
     */
    protected function resolveRawSubstitution(SubstitutionResolution $substitution)
    {
        if ($substitution->factory === null) {
            throw new NotFoundException('Missing raw factory for substitution.');
        }

        return $substitution->factory instanceof \Closure
            ? $substitution->factory
            : ($substitution->factory)(...);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     * @return callable
     */
    protected function resolveConstructorSubstitution(SubstitutionResolution $substitution, $definition, $container)
    {
        if ($substitution->targetId === null) {
            throw new NotFoundException('Missing constructor target for substitution.');
        }

        return $this->buildInstantiationClosure($substitution->targetId, $definition, $container);
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
            return static fn () => $container;
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
        // Call the closure, passing arguments
        return static fn () => $callable(...$params);
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
