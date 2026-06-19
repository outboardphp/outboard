<?php

declare(strict_types=1);

use Outboard\Di\OverrideableDefinitionProvider;
use Outboard\Di\Tests\Fixtures\ArrayDefinitionProvider;
use Outboard\Di\ValueObject\Definition;

describe('OverrideableDefinitionProvider', static function () {
    it('combines framework and app definitions', function () {
        $framework = new ArrayDefinitionProvider([
            'foo' => new Definition(substitute: fn() => 'framework-foo'),
        ]);
        $app = new ArrayDefinitionProvider([
            'bar' => new Definition(substitute: fn() => 'app-bar'),
        ]);

        $provider = new OverrideableDefinitionProvider(
            $framework,
            $app,
            []
        );

        $defs = $provider->getDefinitions();

        expect($defs)->toHaveKey('foo')
            ->and($defs)->toHaveKey('bar')
            ->and($defs['foo']->substitute)->toBeInstanceOf(\Closure::class)
            ->and($defs['bar']->substitute)->toBeInstanceOf(\Closure::class);
    });

    it('throws when app tries to override non-overrideable id', function () {
        $framework = new ArrayDefinitionProvider([
            'foo' => new Definition(substitute: fn() => 'framework-foo'),
        ]);
        $app = new ArrayDefinitionProvider([
            'foo' => new Definition(substitute: fn() => 'app-foo'),
        ]);

        $provider = new OverrideableDefinitionProvider(
            $framework,
            $app,
            [] // foo is not overrideable
        );

        expect(fn() => $provider->getDefinitions())
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'cannot be overridden');
    });

    it('allows override for overrideable ids', function () {
        $framework = new ArrayDefinitionProvider([
            'foo' => new Definition(substitute: fn() => 'framework-foo'),
        ]);
        $app = new ArrayDefinitionProvider([
            'foo' => new Definition(substitute: fn() => 'app-foo'),
        ]);

        $provider = new OverrideableDefinitionProvider(
            $framework,
            $app,
            ['foo']
        );

        $defs = $provider->getDefinitions();

        expect($defs)->toHaveKey('foo');
    });

    it('normalizes definition ids', function () {
        $framework = new ArrayDefinitionProvider([
            'FOO' => new Definition(substitute: fn() => 'upper'),
        ]);
        $app = new ArrayDefinitionProvider([
            'foo' => new Definition(substitute: fn() => 'lower'),
        ]);

        $provider = new OverrideableDefinitionProvider(
            $framework,
            $app,
            ['foo']
        );

        $defs = $provider->getDefinitions();

        expect($defs)->toHaveKey('foo'); // normalized to lowercase
    });
});
