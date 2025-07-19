<?php

use Outboard\Di\DefinitionBuilder;

describe('DefinitionBuilder', static function () {
    it('builds a Definition with default values', function () {
        $builder = new DefinitionBuilder();

        $def = $builder->build();

        expect($def)->toBeInstanceOf(\Outboard\Di\ValueObjects\Definition::class)
            ->and($def->singleton)->toBeFalse()
            ->and($def->strict)->toBeFalse()
            ->and($def->substitute)->toBeNull()
            ->and($def->withParams)->toBe([])
            ->and($def->singletonsInTree)->toBe([])
            ->and($def->call)->toBeNull()
            ->and($def->tags)->toBe([]);
    });

    it('can set all properties', function () {
        $builder = new DefinitionBuilder()
            ->singleton()
            ->strict()
            ->substitute('SomeClass')
            ->withParams(['foo', 'bar'])
            ->singletonsInTree(['id1', 'id2'])
            ->call(static function () {})
            ->tags(['tag1', 'tag2']);

        $def = $builder->build();

        expect($def->singleton)->toBeTrue()
            ->and($def->strict)->toBeTrue()
            ->and($def->substitute)->toBe('SomeClass')
            ->and($def->withParams)->toBe(['foo', 'bar'])
            ->and($def->singletonsInTree)->toBe(['id1', 'id2'])
            ->and($def->call)->toBeCallable()
            ->and($def->tags)->toBe(['tag1', 'tag2']);
    });
});
