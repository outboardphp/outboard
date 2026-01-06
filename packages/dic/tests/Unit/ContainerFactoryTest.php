<?php

use Outboard\Di\ContainerFactory;
use Outboard\Di\ExplicitResolver;
use Outboard\Di\AutowiringResolver;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\Contracts\DefinitionProvider;

describe('ContainerFactory', function () {
    it('creates a container with default resolvers', function () {
        $factory = new ContainerFactory();
        $container = $factory();

        expect($container)->toBeInstanceOf(\Outboard\Di\Container::class);
    });

    it('creates a container with custom resolvers', function () {
        $factory = new ContainerFactory(
            null,
            [ExplicitResolver::class, AutowiringResolver::class]
        );
        $container = $factory();

        expect($container)->toBeInstanceOf(\Outboard\Di\Container::class);
    });

    it('creates a container with definition provider', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'service' => new Definition(
                        substitute: fn() => (object)['value' => 'test'],
                    ),
                ];
            }
        };

        $factory = new ContainerFactory($provider);
        $container = $factory();

        /** @var object{value: string} $result */
        $result = $container->get('service');

        expect($result->value)->toBe('test');
    });

    it('build() is an alias for __invoke()', function () {
        $factory = new ContainerFactory();

        $container1 = $factory();
        $container2 = $factory->build();

        expect($container1)->toBeInstanceOf(\Outboard\Di\Container::class)
            ->and($container2)->toBeInstanceOf(\Outboard\Di\Container::class);
    });

    it('validates configuration during construction', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'valid' => new Definition(
                        substitute: fn() => 'value',
                    ),
                ];
            }
        };

        $factory = new ContainerFactory($provider);

        // Should not throw during validation
        expect(fn() => $factory())->not()->toThrow(Exception::class);
    });

    it('detects circular dependencies during validation', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'a' => new Definition(substitute: 'b'),
                    'b' => new Definition(substitute: 'a'),
                ];
            }
        };

        $factory = new ContainerFactory($provider);

        expect(fn() => $factory())
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'Circular dependency');
    });

    it('detects circular dependencies with multiple steps', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'a' => new Definition(substitute: 'b'),
                    'b' => new Definition(substitute: 'c'),
                    'c' => new Definition(substitute: 'a'),
                ];
            }
        };

        $factory = new ContainerFactory($provider);

        expect(fn() => $factory())
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'Circular dependency');
    });

    it('validates withParams references', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'dependency' => new Definition(
                        substitute: fn() => (object)['val' => 1],
                    ),
                    'service' => new Definition(
                        substitute: fn($dep) => $dep,
                        withParams: ['dependency'],
                    ),
                ];
            }
        };

        $factory = new ContainerFactory($provider);

        // Should validate without throwing
        $container = $factory();

        /** @var object{val: int} $service */
        $service = $container->get('service');
        expect($service->val)->toBe(1);
    });

    it('detects circular dependencies in withParams', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'a' => new Definition(
                        substitute: fn($b) => $b,
                        withParams: ['b'],
                    ),
                    'b' => new Definition(
                        substitute: fn($a) => $a,
                        withParams: ['a'],
                    ),
                ];
            }
        };

        $factory = new ContainerFactory($provider);

        expect(fn() => $factory())
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'Circular dependency');
    });

    it('allows self-referencing in shared instances', function () {
        $provider = new class implements DefinitionProvider {
            public function getDefinitions(): array
            {
                return [
                    'logger' => new Definition(
                        shared: true,
                        substitute: fn() => (object)['name' => 'logger'],
                    ),
                ];
            }
        };

        $factory = new ContainerFactory($provider);
        $container = $factory();

        $logger1 = $container->get('logger');
        $logger2 = $container->get('logger');

        expect($logger1)->toBe($logger2);
    });
});

