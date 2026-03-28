<?php

namespace Outboard\Di\Substitution;

use Outboard\Di\Contracts\SubstitutionHandlerInterface;
use Outboard\Di\Enums\SubstitutionMode;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\SubstitutionResolution;
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
