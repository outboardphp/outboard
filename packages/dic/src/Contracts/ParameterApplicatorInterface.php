<?php

namespace Outboard\Di\Contracts;

use Outboard\Di\ValueObjects\Definition;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

interface ParameterApplicatorInterface
{
    /**
     * @param callable $callable
     * @throws ContainerExceptionInterface
     * @return callable
     */
    public function applyToCallable($callable, Definition $definition, ContainerInterface $container);

    /**
     * @param \Closure $constructorFactory
     * @param class-string $targetId
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     * @return callable
     */
    public function applyToConstructor($constructorFactory, string $targetId, Definition $definition, ContainerInterface $container);
}
