<?php

namespace Outboard\Di;

readonly class ResolvedFactory
{
    public function __construct(
        public \Closure    $factory,
        public ?string     $matchedDefinitionId = null,
        public ?Definition $matchedDefinition = null,
    ) {}
}
