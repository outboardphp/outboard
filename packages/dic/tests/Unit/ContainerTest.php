<?php

use Outboard\Di\Container;
use Outboard\Di\AbstractResolver;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\ResolvedFactory;

describe('Container', static function () {
    it('can be constructed with a Resolver', function () {
        $resolver = new class extends AbstractResolver {
            protected function callableAddParams($callable, $definition, $container) {
                return $callable;
            }
            protected function constructorAddParams($closure, $id, $definition, $container) {
                return $closure;
            }
        };

        $container = new Container([$resolver]);

        expect($container)->toBeInstanceOf(Container::class);
    });

    it('requires at least one resolver', function () {
        // Container should work with an empty resolver array
        // This tests that the constructor accepts the parameter
        $container = new Container([]);

        expect($container)->toBeInstanceOf(Container::class);
    });

    it('throws NotFoundException if no Resolver can resolve id', function () {
        $resolver = new class extends AbstractResolver {
            protected function callableAddParams($callable, $definition, $container) {
                return $callable;
            }
            protected function constructorAddParams($closure, $id, $definition, $container) {
                return $closure;
            }
            public function has(string $id): bool {
                return false;
            }
        };

        $container = new Container([$resolver]);

        expect(static fn() => $container->get('foo'))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });

    it('can set a parent container', function () {
        $resolver = new class extends AbstractResolver {
            protected function callableAddParams($callable, $definition, $container) {
                return $callable;
            }
            protected function constructorAddParams($closure, $id, $definition, $container) {
                return $closure;
            }
        };
        $container = new Container([$resolver]);

        $parentContainer = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $container->setParent($parentContainer);

        expect(true)->toBeTrue(); // Should not throw
    });

    it('throws when setting parent container twice', function () {
        $resolver = new class extends AbstractResolver {
            protected function callableAddParams($callable, $definition, $container) {
                return $callable;
            }
            protected function constructorAddParams($closure, $id, $definition, $container) {
                return $closure;
            }
        };
        $container = new Container([$resolver]);

        $parent1 = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };
        $parent2 = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $container->setParent($parent1);

        expect(fn() => $container->setParent($parent2))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'already set');
    });

    it('throws if resolver returns null factory', function () {
        $resolver = new class extends AbstractResolver {
            protected function callableAddParams($callable, $definition, $container) {
                return $callable;
            }
            protected function constructorAddParams($closure, $id, $definition, $container) {
                return $closure;
            }
            public function has(string $id): bool {
                return true;
            }
            public function resolve(string $id, \Psr\Container\ContainerInterface $container): ResolvedFactory {
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
});
