<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Psr\Container\ContainerInterface;

/**
 * This basic factory class is provided to facilitate decoupled creation of
 * event objects in conjunction with a dependency injection container that
 * complies with the PSR-11 interface. The idea is to typehint this class
 * on a parameter in the constructor of the class where you need to create
 * event objects, and then use the injected factory object to create the
 * events you need rather than using "new" or depending directly on the
 * DI container. Of course, you can always write your own as well if you want
 * more advanced functionality, but this bare minimum enables proper
 * decoupling and it can be easily mocked for testing.
 */
class EventFactory
{
    public function __construct(
        private ContainerInterface $dic,
    ) {}

    public function __invoke(string $fqcn): mixed
    {
        return $this->create($fqcn);
    }

    public function create(string $fqcn): mixed
    {
        return $this->dic->get($fqcn);
    }
}
