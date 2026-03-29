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
        $namedParams = [];
        $numericParams = [];

        foreach ($definition->withParams as $key => $value) {
            if (\is_string($key) && !\is_numeric($key)) {
                $namedParams[$key] = $value;
                continue;
            }

            $numericParams[] = $value;
        }

        $namedParams = $this->referenceResolver->resolve($namedParams, $container);
        $numericParams = $this->referenceResolver->resolve($numericParams, $container);

        $withParams = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $pos = $param->getPosition();
            $type = $param->getType();
            $hasDefault = $param->isDefaultValueAvailable();

            if (isset($namedParams[$name])) {
                // Param was specified by name, so use it.
                $withParams[$name] = $namedParams[$name];
                continue;
            }

            if ($type !== null && !($type instanceof \ReflectionNamedType)) {
                // Param has a type, but it's not a simple named type (scalar or class), so assume union or intersect.
                // Consume a numbered param if available, otherwise throw an error.
                if (\count($numericParams)) {
                    $withParams[$name] = array_shift($numericParams);
                    continue;
                }

                throw new ContainerException("Cannot autowire parameter $pos \"$name\" with union or intersect type; value must be provided");
            }

            if ($type !== null && !$type->isBuiltin()) {
                // Param has a type and it's a class name, so pass it through to be resolved later.
                $withParams[$name] = $type->getName();
                continue;
            }

            if (\count($numericParams)) {
                // Param has no type hint or it's a builtin type, so consume a numbered param.
                $withParams[$name] = array_shift($numericParams);
                continue;
            }

            if (!$hasDefault) {
                throw new ContainerException("Cannot resolve parameter $pos \"$name\"; type must be specified or value must be supplied");
            }

            $withParams[$name] = $param->getDefaultValue();
        }

        if ($remaining = \count($numericParams)) { // intentional assignment
            throw new ContainerException("Too many numeric values supplied to `withParams`; $remaining value(s) were not consumed");
        }

        $resolved = $this->referenceResolver->resolve($withParams, $container);
        return static fn () => $callable(...$resolved);
    }
}
