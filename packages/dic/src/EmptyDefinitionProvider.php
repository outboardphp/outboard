<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contract\DefinitionProvider;

class EmptyDefinitionProvider implements DefinitionProvider
{
    public function getDefinitions(): array
    {
        return [];
    }
}
