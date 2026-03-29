<?php

namespace Outboard\Di\Support;

use Outboard\Di\Contract\ImplicitResolvablePolicyInterface;

class ClassExistsImplicitResolvablePolicy implements ImplicitResolvablePolicyInterface
{
    public function canResolve(string $id): bool
    {
        return \class_exists($id);
    }
}
