<?php

namespace Outboard\Di\Support;

use Outboard\Di\Contract\ImplicitResolvablePolicyInterface;

class NeverImplicitlyResolvablePolicy implements ImplicitResolvablePolicyInterface
{
    public function canResolve(string $id): bool
    {
        return false;
    }
}
