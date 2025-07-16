<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class ExplicitResolver implements Resolver
{
    use Traits\NormalizesId;

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
            // We didn't find a definition, nothing was cached
            return false;
        }
    }

    /**
     * @throws NotFoundException
     */
    public function resolve(string $id, ContainerInterface $container): ResolvedFactory
    {
        // prime the cache or error out
        $this->has($id) || throw $this->notFound($id);

        $rf = $this->definitionLookupCache[$id];

        // generate $closure

        // $rf->factory = $closure;
        return $rf;
    }

    /**
     * Find the definition for the given identifier or throw an exception if not found.
     *
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

        // Next, look for a definition where:
        foreach ($this->definitions as $defId => $definition) {
            if ($defId === '*') {
                // Skip the catch-all definition for now, if it exists
                continue;
            }
            if (
                // The current definition can apply to subclasses, and its id is a parent of our target class
                ($definition->strict === false && \is_subclass_of($id, $defId))
                // or, the id is a regex that matches our target id
                || (@preg_match($defId, $id) === 1)
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
}
