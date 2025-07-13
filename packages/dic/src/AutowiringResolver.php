<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\Resolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class AutowiringResolver implements Resolver
{
    private ContainerInterface $container;
    private array $definitions;

    // Optionally accept config/definitions for autowiring exclusions, preferences, etc.
    public function __construct(ContainerInterface $container, array $definitions = [])
    {
        $this->container = $container;
        $this->definitions = $definitions;
    }

    public function has(string $id): bool
    {
        return class_exists($id);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function resolve(string $id): ResolvedEntry
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
                        $args[] = $this->container->get($depClass);
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        throw new Exception\ContainerException("Cannot resolve parameter '{$param->getName()}' for '$id'.");
                    }
                }
                $instance = $reflection->newInstanceArgs($args);
            }
            // If you have a Definition object for autowiring, pass it; otherwise null
            $defObj = $this->definitions[$id] ?? null;
            return new ResolvedEntry($id, $instance, $defObj instanceof Definition ? $defObj : null);
        } catch (\ReflectionException $e) {
            throw new Exception\ContainerException("Autowiring failed for '$id': " . $e->getMessage(), 0, $e);
        }
    }
}
