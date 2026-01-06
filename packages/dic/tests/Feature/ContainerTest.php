<?php

use Outboard\Di\Container;
use Outboard\Di\ExplicitResolver;
use Outboard\Di\ValueObjects\Definition;

describe('Container Integration', static function () {
    it('can get() items from a resolver', function () {
        $resolver = new ExplicitResolver([
            'foo' => new Definition(substitute: fn() => 'bar'),
        ]);

        $container = new Container([$resolver]);

        expect($container->get('foo'))->toBe('bar');
    });

    it('can use has() to check if items exist in any resolver', function () {
        $resolver = new ExplicitResolver([
            'foo' => new Definition(substitute: fn() => 'bar'),
        ]);

        $container = new Container([$resolver]);

        expect($container->has('foo'))->toBeTrue()
            ->and($container->has('missing'))->toBeFalse();
    });

    it('can populate callable params and call it', function () {
        $resolver = new ExplicitResolver([
            stdClass::class => new Definition(
                substitute: fn() => (object) ['str' => '!'],
            ),
        ]);

        $container = new Container([$resolver]);
        $closure = fn(stdClass $something, int $num, $string) => $something->str . "bar$num$string";

        expect($container->call($closure, ['num' => 1, 2 => 'baz']))->toBe('!bar1baz');
    });

    it('resolves from parent container when not found locally', function () {
        // Create a parent container with a definition
        $parentResolver = new ExplicitResolver([
            'parent-service' => new Definition(substitute: fn() => 'from parent'),
        ]);
        $parentContainer = new Container([$parentResolver]);

        // Create a child container
        $childResolver = new ExplicitResolver([]);
        $childContainer = new Container([$childResolver]);
        $childContainer->setParent($parentContainer);

        expect($childContainer->has('parent-service'))->toBeFalse();
    });

    it('resolves through multiple resolvers', function () {
        $resolver1 = new ExplicitResolver([
            'service1' => new Definition(substitute: fn() => 'from resolver 1'),
        ]);
        $resolver2 = new ExplicitResolver([
            'service2' => new Definition(substitute: fn() => 'from resolver 2'),
        ]);

        $container = new Container([$resolver1, $resolver2]);

        expect($container->get('service1'))->toBe('from resolver 1')
            ->and($container->get('service2'))->toBe('from resolver 2');
    });

    it('uses first resolver that can resolve the id', function () {
        $resolver1 = new ExplicitResolver([
            'service' => new Definition(substitute: fn() => 'from resolver 1'),
        ]);
        $resolver2 = new ExplicitResolver([
            'service' => new Definition(substitute: fn() => 'from resolver 2'),
        ]);

        $container = new Container([$resolver1, $resolver2]);

        expect($container->get('service'))->toBe('from resolver 1');
    });

    it('caches shared instances across multiple get() calls', function () {
        $resolver = new ExplicitResolver([
            'service' => new Definition(
                shared: true,
                substitute: fn() => (object) ['id' => uniqid('', true)],
            ),
        ]);

        $container = new Container([$resolver]);

        $instance1 = $container->get('service');
        $instance2 = $container->get('service');

        expect($instance1)->toBe($instance2);
    });

    it('creates new instances for non-shared definitions', function () {
        $resolver = new ExplicitResolver([
            'service' => new Definition(
                shared: false,
                substitute: fn() => (object) ['id' => uniqid('', true)],
            ),
        ]);

        $container = new Container([$resolver]);

        $instance1 = $container->get('service');
        $instance2 = $container->get('service');

        expect($instance1)->not()->toBe($instance2);
    });
});
