<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Outboard\Wake\Contracts\Hook;

/**
 * Dispatches events to applicable listeners via the registered provider.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        public ListenerProviderInterface $provider,
    ) {}

    /**
     * @param object|object[] $event
     * @throws \Throwable
     * @return object|object[]
     */
    public function __invoke(object|array $event): object|array
    {
        return $this->dispatch($event);
    }

    /**
     * @param object|object[] $event
     * @throws \Throwable
     * @return object|object[]
     */
    public function dispatch(object|array $event): object|array
    {
        if (\is_array($event)) {
            foreach ($event as $subevent) {
                $this->dispatch($subevent);
            }
            return $event;
        }

        /** @var callable $listener */
        foreach ($this->provider->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            try {
                if ($event instanceof Hook) {
                    $event->addResult($listener($event));
                    continue;
                }
                $listener($event);
            } catch (\Throwable $e) {
                // only dispatch a Throwable the first time around -
                // otherwise it could result in an endless loop in the
                // case where a throwable listener throws an exception
                if (!($event instanceof \Throwable)) {
                    $this->dispatch($e);
                }
                throw $e;
            }
        }

        return $event;
    }
}
