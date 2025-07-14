<?php

namespace Outboard\Di\Contracts;

use Outboard\Di\Definition;

interface DefinitionProvider
{
    /**
     * Returns an array of DI definitions.
     *
     * @return array<string, Definition>
     */
    public function getDefinitions(): array;
}
