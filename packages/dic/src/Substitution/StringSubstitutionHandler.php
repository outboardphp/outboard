<?php

namespace Outboard\Di\Substitution;

use Outboard\Di\Contract\SubstitutionHandlerInterface;
use Outboard\Di\Enum\SubstitutionMode;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\SubstitutionResolution;
use Psr\Container\ContainerInterface;

class StringSubstitutionHandler implements SubstitutionHandlerInterface
{
    public function canHandle(Definition $definition): bool
    {
        return \is_string($definition->substitute);
    }

    /**
     * @throws NotFoundException
     */
    public function resolve(
        string $id,
        Definition $definition,
        ContainerInterface $container,
    ): SubstitutionResolution {
        /** @var string $substitute */
        $substitute = $definition->substitute;

        if ($container->has($substitute)) {
            return new SubstitutionResolution(
                factory: static fn () => $container->get($substitute),
                mode: SubstitutionMode::Raw,
            );
        }

        if (!\class_exists($substitute)) {
            throw new NotFoundException(
                "Substitute '{$substitute}' not found for definition '{$id}'",
            );
        }

        return new SubstitutionResolution(
            factory: null,
            mode: SubstitutionMode::Constructor,
            targetId: $substitute,
        );
    }
}
