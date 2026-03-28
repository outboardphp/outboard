<?php

namespace Outboard\Di\Contracts;

use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\SubstitutionResolution;
use Psr\Container\ContainerInterface;

interface SubstitutionResolverInterface
{
    public function resolve(
        string $id,
        Definition $definition,
        ContainerInterface $container,
    ): SubstitutionResolution;
}
