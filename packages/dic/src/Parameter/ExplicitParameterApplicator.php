<?php

namespace Outboard\Di\Parameter;

use Outboard\Di\Contracts\ParameterApplicatorInterface;
use Outboard\Di\Support\ContainerReferenceResolver;
use Outboard\Di\ValueObjects\Definition;
use Psr\Container\ContainerInterface;

readonly class ExplicitParameterApplicator implements ParameterApplicatorInterface
{
    public function __construct(
        private ContainerReferenceResolver $referenceResolver = new ContainerReferenceResolver(),
    ) {}

    public function applyToCallable($callable, Definition $definition, ContainerInterface $container)
    {
        if (!$definition->withParams) {
            return $callable instanceof \Closure ? $callable : $callable(...);
        }

        $params = $this->referenceResolver->resolve($definition->withParams, $container);
        return static fn () => $callable(...$params);
    }

    public function applyToConstructor($constructorFactory, string $targetId, Definition $definition, ContainerInterface $container)
    {
        return $this->applyToCallable($constructorFactory, $definition, $container);
    }
}
