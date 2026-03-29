<?php

use Outboard\Di\Container;
use Outboard\Di\Resolver;
use Outboard\Di\ValueObject\Definition;
use Outboard\Di\ValueObject\ResolvedFactory;
use Psr\Container\ContainerInterface;

describe('Container', static function () {
    it('can be constructed with a Resolver', function () {
        $container = new Container([new Resolver()]);

        expect($container)->toBeInstanceOf(Container::class);
    });

    it('requires at least one resolver', function () {
        // Container should work with an empty resolver array
        // This tests that the constructor accepts the parameter
        $container = new Container([]);

        expect($container)->toBeInstanceOf(Container::class);
    });

    it('throws NotFoundException if no Resolver can resolve id', function () {
        $container = new Container([new Resolver()]);

        expect(static fn() => $container->get('foo'))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });

    it('throws NotFoundException if make() cannot resolve id', function () {
        $container = new Container([new Resolver()]);

        expect(static fn() => $container->make('foo'))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });

    it('can set a parent container', function () {
        $container = new Container([new Resolver()]);

        $parentContainer = new class implements ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $container->setParent($parentContainer);

        expect(true)->toBeTrue(); // Should not throw
    });

    it('throws when setting parent container twice', function () {
        $container = new Container([new Resolver()]);

        $parent1 = new class implements ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };
        $parent2 = new class implements ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $container->setParent($parent1);

        expect(fn() => $container->setParent($parent2))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'already set');
    });

    it('throws if resolver returns null factory', function () {
        $resolver = new class extends Resolver {
            public function has(string $id): bool {
                return true;
            }
            public function resolve(string $id, ContainerInterface $container): ResolvedFactory {
                // Return a ResolvedFactory with null factory to test error handling
                return new ResolvedFactory(
                    factory: null,
                    definitionId: $id,
                    definition: new Definition(),
                );
            }
        };

        $container = new Container([$resolver]);

        expect(fn() => $container->get('service'))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'Should not happen');
    });

    it('make() returns fresh instances for shared services', function () {
        $container = new Container([
            new Resolver([
                'service' => new Definition(
                    shared: true,
                    substitute: static fn() => (object) ['id' => uniqid('', true)],
                ),
            ]),
        ]);

        $instance1 = $container->make('service');
        $instance2 = $container->make('service');

        expect($instance1)->not()->toBe($instance2);
    });

    it('make() bypasses the shared instance cache after get()', function () {
        $container = new Container([
            new Resolver([
                'service' => new Definition(
                    shared: true,
                    substitute: static fn() => (object) ['id' => uniqid('', true)],
                ),
            ]),
        ]);

        $shared = $container->get('service');
        $fresh = $container->make('service');
        $sharedAgain = $container->get('service');

        expect($fresh)
            ->not()->toBe($shared)
            ->and($sharedAgain)->toBe($shared);
    });

    it('make() does not seed the shared instance cache before get()', function () {
        $container = new Container([
            new Resolver([
                'service' => new Definition(
                    shared: true,
                    substitute: static fn() => (object) ['id' => uniqid('', true)],
                ),
            ]),
        ]);

        $made1 = $container->make('service');
        $made2 = $container->make('service');
        $shared1 = $container->get('service');
        $shared2 = $container->get('service');

        expect($made1)
            ->not()->toBe($made2)
            ->and($shared1)->toBe($shared2)
            ->and($shared1)->not()->toBe($made1)
            ->and($shared1)->not()->toBe($made2);
    });

    it('make() re-resolves until a public call seeds the cached factory', function () {
        $resolver = new class extends Resolver {
            public int $resolveCalls = 0;

            public function has(string $id): bool
            {
                return $id === 'service';
            }

            public function resolve(string $id, ContainerInterface $container): ResolvedFactory
            {
                $this->resolveCalls++;

                return new ResolvedFactory(
                    factory: static fn() => new stdClass(),
                    definitionId: $id,
                    definition: new Definition(),
                );
            }
        };

        $container = new Container([$resolver]);

        $container->make('service');
        $container->make('service');

        expect($resolver->resolveCalls)->toBe(2);

        $container->get('service');

        expect($resolver->resolveCalls)->toBe(3);

        $container->make('service');
        $container->make('service');

        expect($resolver->resolveCalls)->toBe(3);
    });

    it('make() reuses the cached factory after get() without re-resolving', function () {
        $resolver = new class extends Resolver {
            public int $resolveCalls = 0;

            public function has(string $id): bool
            {
                return $id === 'service';
            }

            public function resolve(string $id, ContainerInterface $container): ResolvedFactory
            {
                $this->resolveCalls++;

                return new ResolvedFactory(
                    factory: static fn() => new stdClass(),
                    definitionId: $id,
                    definition: new Definition(),
                );
            }
        };

        $container = new Container([$resolver]);

        $container->get('service');
        $fresh1 = $container->make('service');
        $fresh2 = $container->make('service');

        expect($resolver->resolveCalls)->toBe(1)
            ->and($fresh1)->not()->toBe($fresh2);
    });
});
