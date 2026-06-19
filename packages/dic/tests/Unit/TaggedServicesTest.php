<?php

declare(strict_types=1);

use Outboard\Di\Container;
use Outboard\Di\AutowiringResolver;
use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\ValueObject\Definition;

describe('Container::getTagged()', function () {
    it('returns instances of all services tagged with the given tag', function () {
        $container = buildContainer([
            'tagged.service1' => new Definition(substitute: static fn() => (object) ['name' => 'one'], tags: ['my-tag']),
            'tagged.service2' => new Definition(substitute: static fn() => (object) ['name' => 'two'], tags: ['my-tag']),
            'untagged.service' => new Definition(substitute: static fn() => (object) ['name' => 'none'], tags: []),
        ]);

        $instances = $container->getTagged('my-tag');

        expect($instances)->toHaveCount(2)
            ->and($instances[0]->name)->toBe('one')
            ->and($instances[1]->name)->toBe('two');
    });

    it('returns an empty array when no services have the tag', function () {
        $container = buildContainer([
            'service1' => new Definition(substitute: static fn() => (object) ['id' => 1], tags: ['other-tag']),
            'service2' => new Definition(substitute: static fn() => (object) ['id' => 2], tags: []),
        ]);

        $instances = $container->getTagged('my-tag');

        expect($instances)->toBeEmpty();
    });

    it('returns services tagged with multiple tags', function () {
        $container = buildContainer([
            'multi.service' => new Definition(substitute: static fn() => (object) ['value' => 42], tags: ['tag-a', 'tag-b']),
        ]);

        $instancesA = $container->getTagged('tag-a');
        $instancesB = $container->getTagged('tag-b');

        expect($instancesA)->toHaveCount(1)
            ->and($instancesA[0]->value)->toBe(42)
            ->and($instancesB)->toHaveCount(1)
            ->and($instancesB[0]->value)->toBe(42);
    });

    it('respects the shared flag on tagged services', function () {
        $container = buildContainer([
            'shared.service' => new Definition(shared: true, substitute: static fn() => (object) ['id' => uniqid()], tags: ['my-tag']),
        ]);

        $instances1 = $container->getTagged('my-tag');
        $instances2 = $container->getTagged('my-tag');

        expect($instances1[0])->toBe($instances2[0]);
    });

    it('resolves tagged services with autowiring constructor dependencies', function () {
        $container = buildContainer([
            StubbedService::class => new Definition(shared: true, tags: ['event.listener']),
            StubbedServiceB::class => new Definition(shared: true, tags: ['event.listener']),
        ], useAutowiring: true);

        $instances = $container->getTagged('event.listener');

        expect($instances)->toHaveCount(2)
            ->and($instances[0])->toBeInstanceOf(StubbedService::class)
            ->and($instances[1])->toBeInstanceOf(StubbedServiceB::class)
            ->and($instances[1]->dep)->toBeInstanceOf(StubbedService::class);
    });
});

describe('Container::hasTag()', function () {
    it('returns true when services have the tag', function () {
        $container = buildContainer([
            'service' => new Definition(substitute: static fn() => (object) [], tags: ['my-tag']),
        ]);

        expect($container->hasTag('my-tag'))->toBeTrue();
    });

    it('returns false when no services have the tag', function () {
        $container = buildContainer([
            'service' => new Definition(substitute: static fn() => (object) [], tags: ['other-tag']),
        ]);

        expect($container->hasTag('my-tag'))->toBeFalse();
    });

    it('returns false when services have no tags', function () {
        $container = buildContainer([
            'service' => new Definition(substitute: static fn() => (object) [], tags: []),
        ]);

        expect($container->hasTag('my-tag'))->toBeFalse();
    });

    it('returns false on an empty container', function () {
        $container = buildContainer([]);

        expect($container->hasTag('any-tag'))->toBeFalse();
    });

    it('returns true when only one service has the tag among many', function () {
        $container = buildContainer([
            'tagged1' => new Definition(substitute: static fn() => (object) [], tags: ['my-tag']),
            'tagged2' => new Definition(substitute: static fn() => (object) [], tags: ['other']),
            'untagged' => new Definition(substitute: static fn() => (object) [], tags: []),
        ]);

        expect($container->hasTag('my-tag'))->toBeTrue()
            ->and($container->hasTag('other'))->toBeTrue()
            ->and($container->hasTag('missing'))->toBeFalse();
    });
});

function buildContainer(
    array $definitions = [],
    bool $useAutowiring = false,
): Container {
    $provider = new class($definitions) implements DefinitionProvider {
        public function __construct(
            private array $definitions,
        ) {}

        public function getDefinitions(): array
        {
            return $this->definitions;
        }
    };

    if ($useAutowiring) {
        return new Container([AutowiringResolver::create($definitions)]);
    }

    return new Container([new \Outboard\Di\Resolver($definitions)]);
}

class StubbedService
{
    public function __construct(
        public ?string $value = null,
    ) {}
}

class StubbedServiceB
{
    public function __construct(
        public StubbedService $dep,
    ) {}
}
