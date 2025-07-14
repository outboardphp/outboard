<?php

/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpInappropriateInheritDocUsageInspection */

declare(strict_types=1);

namespace Outboard\Di\Traits;

use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;

trait ContainerCommonArrayAccess
{
    /**
     * @inheritDoc
     * @throws ContainerException
     */
    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        if (!\is_string($offset)) {
            throw new ContainerException('Container keys must be strings.');
        }
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     * @throws ContainerExceptionInterface
     */
    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        if (!\is_string($offset)) {
            throw new NotFoundException('Container keys must be strings.');
        }
        return $this->get($offset);
    }

    /**
     * Do not use.
     * @inheritDoc
     * @throws ContainerException
     */
    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ContainerException('Cannot set an instance directly on the container.');
    }

    /**
     * Do not use.
     * @throws ContainerException
     */
    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new ContainerException('Cannot unset an instance from the container.');
    }
}
