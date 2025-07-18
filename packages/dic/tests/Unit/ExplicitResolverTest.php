<?php

describe('ExplicitResolver', function () {
    it('has() returns false if definition not found', function () {
        $resolver = new Outboard\Di\ExplicitResolver([]);

        expect($resolver->has('foo'))->toBeFalse();
    });

    it('has() returns true if definition exists', function () {
        $def = Mockery::mock(Outboard\Di\ValueObjects\Definition::class);

        $resolver = new Outboard\Di\ExplicitResolver(['foo' => $def]);

        expect($resolver->has('foo'))->toBeTrue();
    });

    it('throws NotFoundException when resolving unknown id', function () {
        $container = Mockery::mock(Psr\Container\ContainerInterface::class);

        $resolver = new Outboard\Di\ExplicitResolver([]);

        expect(fn() => $resolver->resolve('bar', $container))
            ->toThrow(Outboard\Di\Exception\NotFoundException::class);
    });

    // Add more tests for resolve, makeClosure, getParams as needed
});

