<?php

namespace Outboard\Di;

use Outboard\Di\Exception\ContainerException;
use Outboard\Di\ValueObjects\Definition;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class AutowiringResolver extends AbstractResolver
{
    /**
     * Checks if this resolver is capable of resolving the given identifier.
     */
    public function has(string $id): bool
    {
        // If we didn't find one, we can autowire it if it exists
        return parent::has($id) || \class_exists($id);
    }

    /**
     * @inheritDoc
     */
    protected function callableAddParams($callable, $definition, $container)
    {
        try {
            $ref = $callable instanceof \Closure
                ? new \ReflectionFunction($callable)
                : new \ReflectionFunction($callable(...));
            return $this->resolveParams($ref, $callable, $definition, $container);
        } catch (\ReflectionException $e) {
            return $callable;
        }
    }

    /**
     * @inheritDoc
     */
    protected function constructorAddParams($closure, $id, $definition, $container)
    {
        $ref = new \ReflectionClass($id)->getConstructor();

        if ($ref === null) {
            return $closure;
        }
        return $this->resolveParams($ref, $closure, $definition, $container);
    }

    /**
     * @param \ReflectionFunctionAbstract $ref A reflected callable or constructor.
     * @param callable $callable The closure we're constructing.
     * @param Definition $definition The definition containing parameters.
     * @param ContainerInterface $container The container to resolve dependencies from.
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     * @return callable
     */
    protected function resolveParams($ref, $callable, $definition, $container)
    {
        $params = $ref->getParameters();
        $withParams = [];
        foreach ($params as $param) {
            // As we loop through the reflected params, pass through any that are found in
            // $definition->withParams and autowire the rest (set them to an id string the
            // container can resolve).
            // $definition->withParams is an array that may have params indexed by name or position.
            // Name keys take precedence over position keys.
            $name = $param->getName();
            $pos = $param->getPosition();
            $type = $param->getType();
            $hasDefault = $param->isDefaultValueAvailable();

            if (isset($definition->withParams[$name])) {
                // Found a named param, so pass it through as-is
                $withParams[$name] = $definition->withParams[$name];
                continue;
            }
            if (
                !isset($withParams[$name])
                && isset($definition->withParams[$pos])
            ) {
                // Found a positional param, so pass it through as-is
                $withParams[$pos] = $definition->withParams[$pos];
                continue;
            }

            // No named or positional param found, so autowire it
            // but only if it's valid for autowiring
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
        $params = $this->getParams($withParams, $container);
        return static function () use ($callable, $params) {
            return $callable(...$params);
        };
    }
}
