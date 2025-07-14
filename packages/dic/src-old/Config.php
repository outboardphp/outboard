<?php

declare(strict_types=1);

namespace Aspire\Di;

use Aspire\Di\Exception\ContainerException;

class Config
{
    /**
     * Returns the rule that will be applied to the class $id during make().
     *
     * @param string $id The name of the ruleset to get - can be a class or not
     * @return Definition that applies when instantiating the given name
     * @throws ContainerException
     */
    public function getRule(string $id): Definition
    {
        if (!$this->rules) {
            $this->load();
        }

        // first, check for exact match
        $normalname = self::normalizeName($id);
        if (isset($this->rules[$normalname])) {
            return $this->rules[$normalname];
        }
        // next, look for a rule where:
        foreach ($this->rules as $key => $rule) {
            if ($key === '*') {
                // skip the default rule, we'll return it at the end if nothing else matches
                continue;
            }
            $matches = [];
            if (
                (   // the rule can apply to subclasses
                    ($rule->inherit === true)
                    // and its name is a parent class of what we're looking for,
                    && \is_subclass_of($id, $key)
                )
                // or the rule is a regex and the id matches it
                || (static::isRegex($key) && \preg_match($key, $id, $matches))
            ) {
                $rule->matches = $matches;
                return $rule;
            }
        }
        // if we get here, return the default rule if it's set
        return $this->rules['*'] ?? new Definition('empty');
    }
}
