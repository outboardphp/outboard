<?php

namespace Outboard\Di\Contract;

interface ImplicitResolvablePolicyInterface
{
    public function canResolve(string $id): bool;
}
