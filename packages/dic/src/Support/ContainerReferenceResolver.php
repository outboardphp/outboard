<?php

namespace Outboard\Di\Support;

use Psr\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;

class ContainerReferenceResolver
{
    /**
     * Resolve string values that reference container IDs.
     *
     * @param mixed[] $params
     * @return mixed[]
     * @throws ContainerExceptionInterface
     */
    public function resolve(array $params, ContainerInterface $container): array
    {
        foreach ($params as &$value) {
            if (\is_string($value) && $container->has($value)) {
                $value = $container->get($value);
            }
        }

        return $params;
    }
}
