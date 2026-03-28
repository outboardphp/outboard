<?php

namespace Outboard\Di\ValueObjects;

use Outboard\Di\Enums\SubstitutionMode;

readonly class SubstitutionResolution
{
    /**
     * @param callable|null $factory
     * @param class-string|null $targetId
     */
    public function __construct(
        public mixed $factory,
        public SubstitutionMode $mode,
        public ?string $targetId = null,
    ) {}
}
