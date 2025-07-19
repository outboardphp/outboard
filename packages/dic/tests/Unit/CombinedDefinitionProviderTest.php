<?php

use Outboard\Di\CombinedDefinitionProvider;
use Outboard\Di\ValueObjects\Definition;

describe('CombinedDefinitionProvider', static function () {
    it('combines definitions from multiple providers', function () {
        $def1 = new Definition();
        $def2 = new Definition();
        $provider1 = new DefinitionProvider(['foo' => $def1]);
        $provider2 = new DefinitionProvider(['bar' => $def2]);

        $combined = new CombinedDefinitionProvider([$provider1, $provider2]);
        $defs = $combined->getDefinitions();

        expect($defs)->toHaveKey('foo')
            ->and($defs)->toHaveKey('bar')
            ->and($defs['foo'])->toBe($def1)
            ->and($defs['bar'])->toBe($def2);
    });

    it('throws on definition collision', function () {
        $def1 = new Definition();
        $def2 = new Definition();
        $provider1 = new DefinitionProvider(['foo' => $def1]);
        $provider2 = new DefinitionProvider(['foo' => $def2]);

        $combined = new CombinedDefinitionProvider([$provider1, $provider2]);

        expect(static fn() => $combined->getDefinitions())
            ->toThrow(\Outboard\Di\Exception\ContainerException::class);
    });

    it('normalizes IDs that are not a regex', function () {
        $def1 = new Definition();
        $provider1 = new DefinitionProvider(['FOO' => $def1]);

        $combined = new CombinedDefinitionProvider([$provider1]);
        $defs = $combined->getDefinitions();

        expect($defs)->toHaveKey('foo'); // normalized to lowercase
    });
});

class DefinitionProvider implements \Outboard\Di\Contracts\DefinitionProvider
{
    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        private readonly array $definitions = [],
    ) {}

    public function getDefinitions(): array
    {
        return $this->definitions;
    }
}
