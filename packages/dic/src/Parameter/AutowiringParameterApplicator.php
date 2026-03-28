<?php

namespace Outboard\Di\Parameter;

use Outboard\Di\Contracts\ParameterApplicatorInterface;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Support\ContainerReferenceResolver;
use Outboard\Di\ValueObjects\Definition;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

readonly class AutowiringParameterApplicator implements ParameterApplicatorInterface
{
    public function __construct(
        private ContainerReferenceResolver $referenceResolver = new ContainerReferenceResolver(),
    ) {}

    public function applyToCallable($callable, Definition $definition, ContainerInterface $container)
    {
        try {
            $ref = $callable instanceof \Closure
                ? new \ReflectionFunction($callable)
                : new \ReflectionFunction($callable(...));
            return $this->resolveParams($ref, $callable, $definition, $container);
        } catch (\ReflectionException) {
            return $callable;
        }
    }

    public function applyToConstructor($constructorFactory, string $targetId, Definition $definition, ContainerInterface $container)
    {
        $ref = new \ReflectionClass($targetId)->getConstructor();
        if ($ref === null) {
            return $constructorFactory;
        }

        return $this->resolveParams($ref, $constructorFactory, $definition, $container);
    }

    /**
     * @param \ReflectionFunctionAbstract $ref
     * @param callable $callable
     * @return callable
     * @throws ContainerExceptionInterface|\ReflectionException
     */
    protected function resolveParams($ref, $callable, Definition $definition, ContainerInterface $container)
    {
        $params = $ref->getParameters();
        $withParams = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $pos = $param->getPosition();
            $type = $param->getType();
            $hasDefault = $param->isDefaultValueAvailable();

            if (isset($definition->withParams[$name])) {
                $withParams[$name] = $definition->withParams[$name];
                continue;
            }

            if (!isset($withParams[$name]) && isset($definition->withParams[$pos])) {
                $withParams[$pos] = $definition->withParams[$pos];
                continue;
            }

            if ($type !== null && !($type instanceof \ReflectionNamedType)) {
                throw new ContainerException("Cannot autowire parameter $pos \"$name\" with union or intersect type");
            }

            if ($type === null || $type->isBuiltin()) {
                if (!$hasDefault) {
                    throw new ContainerException("Cannot autowire parameter $pos \"$name\" without class type hint");
                }
                $withParams[$name] = $param->getDefaultValue();
                continue;
            }

            $withParams[$name] = $type->getName();
        }

        $resolved = $this->referenceResolver->resolve($withParams, $container);
        return static fn () => $callable(...$resolved);
    }
}
