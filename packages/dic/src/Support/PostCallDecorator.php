<?php

namespace Outboard\Di\Support;

use Outboard\Di\ValueObject\Definition;
use Psr\Container\ContainerInterface;

class PostCallDecorator
{
    /**
     * Wrap a factory with post-call behavior when a definition callback exists.
     */
    public function decorate(callable $factory, Definition $definition, ContainerInterface $container): \Closure
    {
        if (!$definition->call) {
            return $factory instanceof \Closure ? $factory : $factory(...);
        }

        return static function () use ($factory, $definition, $container) {
            $object = $factory();
            $return = ($definition->call)($object, $container);
            return $return ?? $object;
        };
    }
}
