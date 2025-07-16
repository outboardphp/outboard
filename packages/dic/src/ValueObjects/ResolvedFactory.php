<?php

namespace Outboard\Di\ValueObjects;

class ResolvedFactory
{
    public function __construct(
        public ?\Closure            $factory = null,
        public readonly ?string     $definitionId = null,
        public readonly ?Definition $definition = null,
    ) {}
}
