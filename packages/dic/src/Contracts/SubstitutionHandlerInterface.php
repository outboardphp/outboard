<?php

namespace Outboard\Di\Contracts;

use Outboard\Di\ValueObjects\Definition;

interface SubstitutionHandlerInterface extends SubstitutionResolverInterface
{
    public function canHandle(Definition $definition): bool;
}
