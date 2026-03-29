<?php

namespace Outboard\Di\Tests\Fixtures;

use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\ValueObject\Definition;

class ArrayDefinitionProvider implements DefinitionProvider
{
    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        private readonly array $definitions = [],
    ) {}

    public function getDefinitions(): array
    {
        return $this->definitions;
    }
}
