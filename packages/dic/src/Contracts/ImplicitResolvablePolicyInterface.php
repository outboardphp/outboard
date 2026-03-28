<?php

namespace Outboard\Di\Contracts;

interface ImplicitResolvablePolicyInterface
{
    public function canResolve(string $id): bool;
}
