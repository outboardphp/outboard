<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\ComposableContainer;
use Outboard\Di\Exception\NotFoundException;
use Outboard\Di\Traits\ContainerCommonArrayAccess;
use Psr\Container\ContainerInterface;

class CompositeContainer implements ContainerInterface, \ArrayAccess
{
    use ContainerCommonArrayAccess;

    /**
     * @param ContainerInterface[] $containers An array of containers to be used sequentially for resolving dependencies.
     */
    public function __construct(protected array $containers = [])
    {
        if (empty($this->containers)) {
            throw new \InvalidArgumentException('At least one container must be provided to the composite container.');
        }

        foreach ($this->containers as $container) {
            if ($container instanceof ComposableContainer) {
                $container->setParent($this);
            }
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function has(string $id): bool
    {
        return \array_any($this->containers, fn($container) => $container->has($id));
    }

    /**
     * @inheritDoc
     * @template T
     * @param string|class-string<T> $id Identifier of the entry to look for.
     * @return T|mixed|null
     */
    #[\Override]
    public function get(string $id)
    {
        $foundInContainer = \array_find($this->containers, fn($container) => $container->has($id));
        if ($foundInContainer !== null) {
            return $foundInContainer->get($id);
        }
        throw new NotFoundException("No entry was found for '$id'.");
    }
}
