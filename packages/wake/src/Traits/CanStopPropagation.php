<?php

namespace Outboard\Wake\Traits;

/**
 * A simple implementation for Psr\EventDispatcher\StoppableEventInterface
 *
 * @phpstan-ignore trait.unused
 */
trait CanStopPropagation
{
    /** @var bool Indicates whether the event should be handled by further listeners */
    protected $stopPropagation = false;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Dispatcher to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Event is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }
}
