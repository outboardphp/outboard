<?php

namespace Outboard\Di\Support;

class DefinitionIdNormalizer
{
    public function __construct(
        protected ?RegexPatternMatcher $regexPatternMatcher = new RegexPatternMatcher(),
    ) {
    }

    /**
     * @param string $id
     * @return string lowercased classname without a leading backslash
     */
    public function normalizeLookupId($id)
    {
        return \strtolower(\ltrim($id, '\\'));
    }

    /**
     * Normalizes a definition id unless it is a regex pattern or catch-all.
     */
    public function normalizeDefinitionId(string $id): string
    {
        if ($id === '*' || $this->isRegexPattern($id)) {
            return $id;
        }

        return $this->normalizeLookupId($id);
    }

    public function isRegexPattern(string $pattern): bool
    {
        return $this->regexPatternMatcher->isPattern($pattern);
    }
}
