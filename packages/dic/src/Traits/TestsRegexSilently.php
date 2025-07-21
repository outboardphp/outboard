<?php

declare(strict_types=1);

namespace Outboard\Di\Traits;

trait TestsRegexSilently
{
    /**
     * @param string $pattern The string to test as a regex pattern
     * @param string $subject The subject to test against the pattern
     * @return false|int
     */
    protected static function testRegexSilently($pattern, $subject = '')
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
