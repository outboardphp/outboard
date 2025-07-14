<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Mapper from an event to the listeners that are applicable to that event.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
readonly class ListenerProvider implements ListenerProviderInterface
{
    public function __construct(public ListenerCollection $listeners) {}

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->listeners->listeners as $type => $listeners) {
            if ($event instanceof $type) {
                yield from $listeners;
            }
        }
    }
}
