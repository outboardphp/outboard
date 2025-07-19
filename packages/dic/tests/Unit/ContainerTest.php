<?php

use Outboard\Di\Container;
use Outboard\Di\Contracts\Resolver;

describe('Container', static function () {
    it('can be constructed with a Resolver', function () {
        $resolver = \Mockery::mock(Resolver::class);

        $container = new Container([$resolver]);

        expect($container)->toBeInstanceOf(Container::class);
    });

    it('throws NotFoundException if no Resolver can resolve id', function () {
        $resolver = \Mockery::mock(Resolver::class);
        $resolver->shouldReceive('has')->andReturn(false);

        $container = new Container([$resolver]);

        expect(static fn() => $container->get('foo'))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });

    it('delegates get() to resolver', function () {
        $resolver = \Mockery::mock(Resolver::class);
        $resolver->shouldReceive('has')->andReturn(true);
        $resolver->shouldReceive('resolve')
            ->andReturn(new \Outboard\Di\ValueObjects\ResolvedFactory(
                static fn() => 'bar',
                'foo',
                new \Outboard\Di\ValueObjects\Definition(),
            ));

        $container = new Container([$resolver]);

        expect($container->get('foo'))->toBe('bar');
    });

    it('delegates has() to resolver', function () {
        $resolver = \Mockery::mock(Resolver::class);
        $resolver->shouldReceive('has')->andReturn(true);

        $container = new Container([$resolver]);

        expect($container->has('foo'))->toBeTrue();
    });
});
