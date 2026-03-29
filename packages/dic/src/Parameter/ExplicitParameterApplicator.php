<?php

namespace Outboard\Di\Parameter;

use Outboard\Di\Container;
use Outboard\Di\Contract\ParameterApplicatorInterface;
use Outboard\Di\Support\ContainerReferenceResolver;
use Outboard\Di\ValueObject\Definition;
use Psr\Container\ContainerInterface;

readonly class ExplicitParameterApplicator implements ParameterApplicatorInterface
{
    public function __construct(
        private ContainerReferenceResolver $referenceResolver = new ContainerReferenceResolver(),
    ) {}

    public function applyToCallable($callable, Definition $definition, ContainerInterface $container)
    {
        $withParams = $this->referenceResolver->resolve($definition->withParams, $container);

        if ($container instanceof Container) {
            return static fn () => $container->call($callable, $withParams);
        }

        return static fn () => $callable(...$withParams);
    }

    public function applyToConstructor($constructorFactory, string $targetId, Definition $definition, ContainerInterface $container)
    {
        if (!$definition->withParams) {
            return $constructorFactory instanceof \Closure ? $constructorFactory : $constructorFactory(...);
        }

        $params = $this->referenceResolver->resolve($definition->withParams, $container);
        return static fn () => $constructorFactory(...$params);
    }
}
