<?php

namespace Outboard\Di\Matching;

use Outboard\Di\ValueObject\Definition;

class SubclassMatchStrategy
{
    public function matches(string $id, string $definitionId, Definition $definition): bool
    {
        return $definition->strict === false && \is_subclass_of($id, $definitionId);
    }
}
