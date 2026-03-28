<?php

namespace Outboard\Di\Matching;

use Outboard\Di\Support\RegexPatternMatcher;

class RegexMatchStrategy
{
    public function __construct(
        protected ?RegexPatternMatcher $regexPatternMatcher = new RegexPatternMatcher(),
    ) {
    }

    public function matches(string $id, string $definitionId): bool
    {
        return $this->regexPatternMatcher->matches($definitionId, $id);
    }
}
