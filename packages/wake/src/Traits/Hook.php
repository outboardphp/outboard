<?php

declare(strict_types=1);

namespace Outboard\Wake\Traits;

/**
 * @phpstan-ignore trait.unused
 */
trait Hook
{
    /** @var array Contains the return values of previously-called event listeners */
    private $results = [];

    /**
     * Add returned value from a listener
     *
     * @param mixed $val The value to add to the results array
     * @return mixed the result that was added
     */
    public function addResult($val = null)
    {
        if ($val !== null) {
            $this->results[] = $val;
        }
        return $val;
    }

    /**
     * Get the last value returned from listeners
     */
    public function getResult(): array
    {
        return end($this->results);
    }

    /**
     * Get array of all values returned from listeners
     *
     * @return mixed[]
     */
    public function getAllResults(): array
    {
        return $this->results;
    }
}
