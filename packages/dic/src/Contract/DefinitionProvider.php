<?php

namespace Outboard\Di\Contract;

use Outboard\Di\ValueObject\Definition;

interface DefinitionProvider
{
    /**
     * Returns an array of DI definitions.
     *
     * @return array<string, Definition>
     */
    public function getDefinitions(): array;
}
