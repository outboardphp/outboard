<?php

namespace Outboard\Di;

readonly class ResolvedEntry
{
    public function __construct(
        public string      $id,
        public mixed       $instance,
        public ?Definition $definition = null
    ) {}
}
