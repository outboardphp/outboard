<?php

namespace Outboard\Di\Substitution;

use Outboard\Di\Contracts\SubstitutionHandlerInterface;
use Outboard\Di\Enums\SubstitutionMode;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\SubstitutionResolution;
use Psr\Container\ContainerInterface;

class CallableSubstitutionHandler implements SubstitutionHandlerInterface
{
    public function canHandle(Definition $definition): bool
    {
        return \is_callable($definition->substitute);
    }

    public function resolve(
        string $id,
        Definition $definition,
        ContainerInterface $container,
    ): SubstitutionResolution {
        return new SubstitutionResolution(
            factory: ($definition->substitute)(...),
            mode: SubstitutionMode::Callable,
        );
    }
}
