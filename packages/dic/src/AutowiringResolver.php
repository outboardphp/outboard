<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\DefinitionProvider;
use Outboard\Di\Contracts\Resolver;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\ResolvedFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class AutowiringResolver implements Resolver
{
    /**
     * Optionally accepts config/definitions for autowiring exclusions, preferences, etc.
     */
    public function __construct(
        protected ?DefinitionProvider $definitions = null,
    ) {}

    public function has(string $id): bool
    {
        return class_exists($id);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function resolve(string $id, ContainerInterface $container): ResolvedFactory
    {
        if (!class_exists($id)) {
            throw new Exception\NotFoundException("Cannot autowire '$id': class does not exist.");
        }
        try {
            $reflection = new \ReflectionClass($id);
            $constructor = $reflection->getConstructor();
            if (!$constructor) {
                $instance = new $id();
            } else {
                $params = $constructor->getParameters();
                $args = [];
                foreach ($params as $param) {
                    $type = $param->getType();
                    if ($type && !$type->isBuiltin()) {
                        $depClass = $type->getName();
                        $args[] = $container->get($depClass);
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        throw new ContainerException("Cannot resolve parameter '{$param->getName()}' for '$id'.");
                    }
                }
                $instance = $reflection->newInstanceArgs($args);
            }
            // If you have a Definition object for autowiring, pass it; otherwise null
            $defObj = $this->definitions[$id] ?? null;
            return new ResolvedFactory($instance, $id, $defObj instanceof Definition ? $defObj : null);
        } catch (\ReflectionException $e) {
            throw new ContainerException("Autowiring failed for '$id': " . $e->getMessage(), 0, $e);
        }
    }
}
