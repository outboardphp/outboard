<?php

describe('CompositeContainer', function () {
    it('errors if constructed with no containers', function () {
        expect(fn() => new Outboard\Di\CompositeContainer([]))
            ->toThrow(InvalidArgumentException::class);
    });

    it('can resolve an entry from the first container that has it', function () {
        $container1 = Mockery::mock(Psr\Container\ContainerInterface::class);
        $container1->shouldReceive('has')->with('foo')->andReturn(true);
        $container1->shouldReceive('get')->with('foo')->andReturn('bar');
        $container2 = Mockery::mock(Psr\Container\ContainerInterface::class);
        $container2->shouldReceive('has')->with('foo')->andReturn(false);

        $composite = new Outboard\Di\CompositeContainer([$container1, $container2]);

        expect($composite->get('foo'))->toBe('bar');
    });

    it('throws NotFoundException if no container can resolve id', function () {
        $container1 = Mockery::mock(Psr\Container\ContainerInterface::class);
        $container1->shouldReceive('has')->andReturn(false);

        $composite = new Outboard\Di\CompositeContainer([$container1]);

        expect(fn() => $composite->get('missing'))
            ->toThrow(Outboard\Di\Exception\NotFoundException::class);
    });
});

