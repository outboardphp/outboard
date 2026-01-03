<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * This basic factory class is provided to facilitate decoupled creation of
 * event objects in conjunction with a dependency injection container that
 * complies with the PSR-11 interface. Typehint this class on a constructor
 * parameter of the class where you need it, and then use that factory
 * object to create the event objects you need rather than using "new" or
 * depending directly on the DI container.
 * Of course, you can always write your own if you want more advanced
 * functionality, but this bare minimum enables proper decoupling, and
 * it can be easily mocked for testing.
 */
readonly class EventFactory
{
    public function __construct(
        private ContainerInterface $dic,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(string $fqcn): mixed
    {
        return $this->create($fqcn);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(string $fqcn): mixed
    {
        return $this->dic->get($fqcn);
    }
}
