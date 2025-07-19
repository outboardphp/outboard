<?php

use Outboard\Di\CompositeContainer;

describe('CompositeContainer', static function () {
    it('errors if constructed with no containers', function () {
        expect(static fn() => new CompositeContainer([]))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('can resolve an entry from the first container that has it', function () {
        $container1 = new Container(['foo' => 'bar']);
        $container2 = new Container([]);
        $composite = new CompositeContainer([$container1, $container2]);
        expect($composite->get('foo'))->toBe('bar');
    });

    it('throws NotFoundException if no container can resolve id', function () {
        $container1 = new Container([]);
        $composite = new CompositeContainer([$container1]);
        expect(static fn() => $composite->get('missing'))
            ->toThrow(\Outboard\Di\Exception\NotFoundException::class);
    });
});

class Container implements \Psr\Container\ContainerInterface
{
    /**
     * @param array<string, mixed> $entries
     */
    public function __construct(
        private readonly array $entries = [],
    ) {}

    public function has($id): bool
    {
        return array_key_exists($id, $this->entries);
    }

    public function get($id)
    {
        return $this->entries[$id] ?? null;
    }
}
