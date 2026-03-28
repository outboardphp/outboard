<?php

namespace Outboard\Di\Support;

class RegexPatternMatcher
{
    /**
     * Detect whether a string is a valid regex pattern.
     */
    public function isPattern(string $pattern): bool
    {
        return $this->testSilently($pattern) !== false;
    }

    /**
     * Test a regex pattern without emitting warnings.
     */
    public function matches(string $pattern, string $subject): bool
    {
        return $this->testSilently($pattern, $subject) === 1;
    }

    /**
     * @param string $pattern The string to test as a regex pattern
     * @param string $subject The subject to test against the pattern
     * @return false|int
     */
    protected function testSilently($pattern, $subject = '')
    {
        \set_error_handler(static function () { return true; });

        try {
            $isRegex = \preg_match($pattern, $subject);
        } catch (\Throwable) {
            $isRegex = false;
        } finally {
            \restore_error_handler();
        }

        return $isRegex;
    }
}

