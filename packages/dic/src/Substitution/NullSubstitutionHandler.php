<?php

namespace Outboard\Di\Substitution;

use Outboard\Di\Contract\SubstitutionHandlerInterface;
use Outboard\Di\Enum\SubstitutionMode;
use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\SubstitutionResolution;
use Psr\Container\ContainerInterface;

class NullSubstitutionHandler implements SubstitutionHandlerInterface
{
    public function canHandle(Definition $definition): bool
    {
        return $definition->substitute === null;
    }

    public function resolve(
        string $id,
        Definition $definition,
        ContainerInterface $container,
    ): SubstitutionResolution {
        return new SubstitutionResolution(
            factory: null,
            mode: SubstitutionMode::Constructor,
            targetId: $id,
        );
    }
}
