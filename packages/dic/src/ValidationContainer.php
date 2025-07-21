<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class ValidationContainer implements ContainerInterface
{
    use Traits\NormalizesId;

    /** @var string[][] */
    protected array $resolutionStack = [];

    /**
     * @param Resolver[] $resolvers
     */
    public function __construct(
        protected array $resolvers,
    ) {}

    /**
     * @inheritDoc
     * @template T
     * @param class-string<T>|string $id Identifier of the entry to look for.
     * @throws ContainerException
     * @phpstan-ignore method.templateTypeNotInParameter
     */
    public function get(string $id)
    {
        // Find a resolver that can resolve this id
        $resolver = \array_find($this->resolvers, fn($resolver) => $resolver->has($id));
        if (!($resolver instanceof Resolver)) {
            throw new NotFoundException("No resolver was found for '{$id}' during validation.");
        }

        $thisId = [$resolver::class, static::normalizeId($id)];
        if (\in_array($thisId, $this->resolutionStack, true)) {
            throw new ContainerException('Circular dependency detected: '
                . \implode(' -> ', \array_merge(
                    \array_column($this->resolutionStack, 1),
                    [$id],
                )));
        }

        $this->resolutionStack[] = $thisId;

        $definition = $resolver->resolve($id, $this)->definition;

        // Validate substitute
        if (\is_string($definition?->substitute) && $this->has($definition->substitute)) {
            $this->get($definition->substitute);
        }
        // Validate withParams (if they reference other services)
        if (!empty($definition->withParams)) {
            foreach ($definition->withParams as $param) {
                if (\is_string($param) && $this->has($param)) {
                    $this->get($param);
                }
            }
        }
        \array_pop($this->resolutionStack);
        // No instance is constructed
    }

    public function has(string $id): bool
    {
        return \array_any($this->resolvers, fn($resolver) => $resolver->has($id));
    }
}
