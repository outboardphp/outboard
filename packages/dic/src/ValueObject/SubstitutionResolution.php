<?php

namespace Outboard\Di\ValueObject;

use Outboard\Di\Enum\SubstitutionMode;

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
