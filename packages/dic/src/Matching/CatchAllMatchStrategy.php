<?php

namespace Outboard\Di\Matching;

use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\ResolvedFactory;

class CatchAllMatchStrategy
{
    /**
     * @param array<string, Definition> $definitions
     */
    public function tryMatch(string $id, array $definitions): ?ResolvedFactory
    {
        if (
            !isset($definitions['*'])
            || (
                !\class_exists($id) && !\interface_exists($id) && !isset($definitions['*']->substitute)
            )
        ) {
            return null;
        }

        return new ResolvedFactory(
            definitionId: '*',
            definition: $definitions['*'],
        );
    }
}
