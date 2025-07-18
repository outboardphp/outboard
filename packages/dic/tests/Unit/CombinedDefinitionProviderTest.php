<?php

describe('CombinedDefinitionProvider', function () {
    it('combines definitions from multiple providers', function () {
        $def1 = Mockery::mock(Outboard\Di\ValueObjects\Definition::class);
        $def2 = Mockery::mock(Outboard\Di\ValueObjects\Definition::class);
        $provider1 = Mockery::mock(Outboard\Di\Contracts\DefinitionProvider::class);
        $provider2 = Mockery::mock(Outboard\Di\Contracts\DefinitionProvider::class);
        $provider1->shouldReceive('getDefinitions')->andReturn(['foo' => $def1]);
        $provider2->shouldReceive('getDefinitions')->andReturn(['bar' => $def2]);

        $combined = new Outboard\Di\CombinedDefinitionProvider([$provider1, $provider2]);
        $defs = $combined->getDefinitions();

        expect($defs)->toHaveKey('foo')
            ->and($defs)->toHaveKey('bar')
            ->and($defs['foo'])->toBe($def1)
            ->and($defs['bar'])->toBe($def2);
    });

    it('throws on definition collision', function () {
        $def1 = Mockery::mock(Outboard\Di\ValueObjects\Definition::class);
        $def2 = Mockery::mock(Outboard\Di\ValueObjects\Definition::class);
        $provider1 = Mockery::mock(Outboard\Di\Contracts\DefinitionProvider::class);
        $provider2 = Mockery::mock(Outboard\Di\Contracts\DefinitionProvider::class);
        $provider1->shouldReceive('getDefinitions')->andReturn(['foo' => $def1]);
        $provider2->shouldReceive('getDefinitions')->andReturn(['foo' => $def2]);

        $combined = new Outboard\Di\CombinedDefinitionProvider([$provider1, $provider2]);

        expect(fn() => $combined->getDefinitions())
            ->toThrow(Outboard\Di\Exception\ContainerException::class);
    });

    it('normalizes IDs that are not a regex', function () {
        $def1 = Mockery::mock(Outboard\Di\ValueObjects\Definition::class);
        $provider1 = Mockery::mock(Outboard\Di\Contracts\DefinitionProvider::class);
        $provider1->shouldReceive('getDefinitions')->andReturn(['FOO' => $def1]);

        $combined = new Outboard\Di\CombinedDefinitionProvider([$provider1]);
        $defs = $combined->getDefinitions();

        expect($defs)->toHaveKey('foo'); // normalized to lowercase
    });
});

