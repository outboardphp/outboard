<?php

namespace Outboard\Di;

use Outboard\Di\Contract\ImplicitResolvablePolicyInterface;
use Outboard\Di\Contract\ParameterApplicatorInterface;
use Outboard\Di\Contract\SubstitutionResolverInterface;
use Outboard\Di\Enum\SubstitutionMode;
use Outboard\Di\Matching\DefinitionMatcher;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\Parameter\ExplicitParameterApplicator;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\Support\NeverImplicitlyResolvablePolicy;
use Outboard\Di\Support\PostCallDecorator;
use Outboard\Di\Substitution\SubstitutionResolverChain;
use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\ResolvedFactory;
use Outboard\Di\ValueObject\SubstitutionResolution;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class Resolver
{
    /** @var array<string, ?ResolvedFactory> */
    protected array $definitionLookupCache = [];

    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        protected array $definitions = [],
        protected DefinitionIdNormalizer $definitionIdNormalizer = new DefinitionIdNormalizer(),
        protected DefinitionMatcher $definitionMatcher = new DefinitionMatcher(),
        protected SubstitutionResolverInterface $substitutionResolver = new SubstitutionResolverChain(),
        protected PostCallDecorator $postCallDecorator = new PostCallDecorator(),
        protected ParameterApplicatorInterface $parameterApplicator = new ExplicitParameterApplicator(),
        protected ImplicitResolvablePolicyInterface $implicitResolvablePolicy = new NeverImplicitlyResolvablePolicy(),
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
        // No definition matched; defer to resolver policy for implicit resolvability.
        return $this->implicitResolvablePolicy->canResolve($id);
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

        return $this->parameterApplicator->applyToCallable($substitution->factory, $definition, $container);
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
        return $this->parameterApplicator->applyToConstructor($factory, $targetId, $definition, $container);
    }
}
