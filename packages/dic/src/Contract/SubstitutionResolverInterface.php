<?php

namespace Outboard\Di\Contract;

use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\SubstitutionResolution;
use Psr\Container\ContainerInterface;

interface SubstitutionResolverInterface
{
    public function resolve(
        string $id,
        Definition $definition,
        ContainerInterface $container,
    ): SubstitutionResolution;
}
