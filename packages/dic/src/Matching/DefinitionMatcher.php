<?php

namespace Outboard\Di\Matching;

use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\ResolvedFactory;

class DefinitionMatcher
{
    public function __construct(
        protected ExactMatchStrategy $exactMatch = new ExactMatchStrategy(),
        protected SubclassMatchStrategy $subclassMatch = new SubclassMatchStrategy(),
        protected RegexMatchStrategy $regexMatch = new RegexMatchStrategy(),
        protected CatchAllMatchStrategy $catchAllMatch = new CatchAllMatchStrategy(),
    ) {
    }

    /**
     * @param array<string, Definition> $definitions
     */
    public function match(string $id, array $definitions): ?ResolvedFactory
    {
        $exact = $this->exactMatch->tryMatch($id, $definitions);
        if ($exact !== null) {
            return $exact;
        }

        // Preserve existing precedence by evaluating subclass and regex per-definition in order.
        foreach ($definitions as $definitionId => $definition) {
            if ($definitionId === '*') {
                continue;
            }

            if (
                $this->subclassMatch->matches($id, $definitionId, $definition)
                || $this->regexMatch->matches($id, $definitionId)
            ) {
                return new ResolvedFactory(
                    definitionId: $definitionId,
                    definition: $definition,
                );
            }
        }

        return $this->catchAllMatch->tryMatch($id, $definitions);
    }
}
