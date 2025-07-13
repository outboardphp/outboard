<?php

declare(strict_types=1);

namespace Outboard\Wake\Contracts;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * An Event whose processing may be interrupted by outside code.
 * Used together with the CanBeStopped trait.
 */
interface ForceStop extends StoppableEventInterface
{
    /**
     * Determine whether this event should be sent to further listeners
     *
     * @return bool whether propagation was stopped
     */
    public function stopPropagation(bool $stop = true): bool;
}
