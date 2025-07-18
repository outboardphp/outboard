<?php

describe('Container', function () {
    it('can be constructed with a Resolver', function () {
        $resolver = Mockery::mock(Outboard\Di\Contracts\Resolver::class);

        $container = new Outboard\Di\Container([$resolver]);

        expect($container)->toBeInstanceOf(Outboard\Di\Container::class);
    });

    it('throws NotFoundException if no Resolver can resolve id', function () {
        $resolver = Mockery::mock(Outboard\Di\Contracts\Resolver::class);
        $resolver->shouldReceive('has')->andReturn(false);

        $container = new Outboard\Di\Container([$resolver]);

        expect(fn() => $container->get('foo'))
            ->toThrow(Outboard\Di\Exception\NotFoundException::class);
    });

    // Add more tests for get, has, setParent, singleton/factory behavior as needed
});

