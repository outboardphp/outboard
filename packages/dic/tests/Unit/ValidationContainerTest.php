<?php

declare(strict_types=1);

use Outboard\Di\ValidationContainer;
use Outboard\Di\Resolver;
use Outboard\Di\ValueObject\Definition;

describe('ValidationContainer', static function () {
    it('validates without constructing instances', function () {
        $resolver = new Resolver([
            TestService::class => new Definition(),
        ]);

        $container = new ValidationContainer([$resolver]);

        // Should not throw
        $container->get(TestService::class);

        expect(true)->toBeTrue();
    });

    it('detects circular dependencies during validation', function () {
        $resolver = new Resolver([
            'a' => new Definition(substitute: 'b'),
            'b' => new Definition(substitute: 'a'),
        ]);

        $container = new ValidationContainer([$resolver]);

        expect(fn() => $container->get('a'))
            ->toThrow(\Outboard\Di\Exception\ContainerException::class, 'Circular dependency');
    });

    it('validates substitute references', function () {
        $resolver = new Resolver([
            'service' => new Definition(substitute: TestService::class),
        ]);

        $container = new ValidationContainer([$resolver]);

        // Should not throw even though we're not actually constructing anything
        $container->get('service');

        expect(true)->toBeTrue();
    });

    it('validates withParams references', function () {
        $resolver = new Resolver([
            'dependency' => new Definition(substitute: TestService::class),
            'service' => new Definition(
                substitute: fn($dep) => $dep,
                withParams: ['dependency']
            ),
        ]);

        $container = new ValidationContainer([$resolver]);

        // Should not throw
        $container->get('service');

        expect(true)->toBeTrue();
    });
});

class TestService
{
    public function __construct() {}
}