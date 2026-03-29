<?php

namespace Outboard\Di\Substitution;

use Outboard\Di\Contract\SubstitutionHandlerInterface;
use Outboard\Di\Enum\SubstitutionMode;
use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\SubstitutionResolution;
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
