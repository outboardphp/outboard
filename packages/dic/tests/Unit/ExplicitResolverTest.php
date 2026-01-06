<?php

use Outboard\Di\ExplicitResolver;
use Outboard\Di\ValueObjects\Definition;

describe('ExplicitResolver', static function () {
    it('has() returns false if definition not found', function () {
        $resolver = new ExplicitResolver([]);

        expect($resolver->has('foo'))->toBeFalse();
    });

    it('has() returns true if definition exists', function () {
        $def = new Definition();

        $resolver = new ExplicitResolver(['foo' => $def]);

        expect($resolver->has('foo'))->toBeTrue();
    });

    it('throws NotFoundException when resolving unknown id', function () {
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $resolver = new ExplicitResolver([]);

        expect(static fn() => $resolver->resolve('bar', $container))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });

    it('resolves a simple callable substitute', function () {
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $resolver = new ExplicitResolver([
            'service' => new Definition(substitute: fn() => 'result'),
        ]);

        $resolved = $resolver->resolve('service', $container);

        expect($resolved->factory)->toBeCallable()
            ->and(($resolved->factory)())->toBe('result');
    });

    it('resolves with withParams', function () {
        $container = new class implements \Psr\Container\ContainerInterface {
            public function has(string $id): bool { return false; }
            public function get(string $id) { return null; }
        };

        $resolver = new ExplicitResolver([
            'service' => new Definition(
                substitute: fn($a, $b) => $a + $b,
                withParams: [10, 20],
            ),
        ]);

        $resolved = $resolver->resolve('service', $container);

        expect(($resolved->factory)())->toBe(30);
    });
});
