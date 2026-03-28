<?php

namespace Outboard\Di\Matching;

use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\ResolvedFactory;

class ExactMatchStrategy
{
    public function __construct(
        protected ?DefinitionIdNormalizer $definitionIdNormalizer = new DefinitionIdNormalizer(),
    ) {
    }

    /**
     * @param array<string, Definition> $definitions
     */
    public function tryMatch(string $id, array $definitions): ?ResolvedFactory
    {
        $normalizedId = $this->definitionIdNormalizer->normalizeLookupId($id);

        if (!isset($definitions[$normalizedId])) {
            return null;
        }

        return new ResolvedFactory(
            definitionId: $normalizedId,
            definition: $definitions[$normalizedId],
        );
    }
}
