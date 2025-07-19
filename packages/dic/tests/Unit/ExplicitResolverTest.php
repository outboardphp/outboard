<?php

use Outboard\Di\ExplicitResolver;

describe('ExplicitResolver', function () {
    it('has() returns false if definition not found', function () {
        $resolver = new ExplicitResolver([]);

        expect($resolver->has('foo'))->toBeFalse();
    });

    it('has() returns true if definition exists', function () {
        $def = new Outboard\Di\ValueObjects\Definition();

        $resolver = new ExplicitResolver(['foo' => $def]);

        expect($resolver->has('foo'))->toBeTrue();
    });

    it('throws NotFoundException when resolving unknown id', function () {
        $container = Mockery::mock(Psr\Container\ContainerInterface::class);

        $resolver = new ExplicitResolver([]);

        expect(fn() => $resolver->resolve('bar', $container))
            ->toThrow(Outboard\Di\Exception\NotFoundException::class);
    });

    // Add more tests for resolve, makeClosure, getParams as needed
});

