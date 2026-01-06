<?php

namespace Outboard\Di\Traits;

trait NormalizesId
{
    /**
     * @param string $name
     * @return string lowercased classname without a leading backslash
     */
    protected static function normalizeId($name)
    {
        return \strtolower(\ltrim($name, '\\'));
    }
}
