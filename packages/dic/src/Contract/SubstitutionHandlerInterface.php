<?php

namespace Outboard\Di\Contract;

use Outboard\Di\ValueObject\Definition;

interface SubstitutionHandlerInterface extends SubstitutionResolverInterface
{
    public function canHandle(Definition $definition): bool;
}
