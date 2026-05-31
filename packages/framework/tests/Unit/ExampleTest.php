<?php

use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\OverrideableDefinitionProvider;
use Outboard\Di\ValueObject\Definition;

it('allows overriding only explicitly-overrideable framework defaults', function () {
    $frameworkProvider = new class implements DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                'overrideable.service' => new Definition(substitute: static fn() => 'framework-default'),
                'framework.internal' => new Definition(substitute: static fn() => 'internal-default'),
            ];
        }
    };

    $appProvider = new class implements DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                'overrideable.service' => new Definition(substitute: static fn() => 'app-override'),
            ];
        }
    };

    $provider = new OverrideableDefinitionProvider($frameworkProvider, $appProvider, ['overrideable.service']);
    $definitions = $provider->getDefinitions();

    expect($definitions['overrideable.service']->substitute)->toBeCallable();
    expect($definitions['framework.internal']->substitute)->toBeCallable();
});

it('throws on collisions for non-overrideable definitions', function () {
    $frameworkProvider = new class implements DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                'framework.internal' => new Definition(substitute: static fn() => 'framework-default'),
            ];
        }
    };

    $appProvider = new class implements DefinitionProvider {
        public function getDefinitions(): array
        {
            return [
                'framework.internal' => new Definition(substitute: static fn() => 'app-value'),
            ];
        }
    };

    $provider = new OverrideableDefinitionProvider($frameworkProvider, $appProvider, []);

    expect(static fn() => $provider->getDefinitions())
        ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'cannot be overridden');
});
