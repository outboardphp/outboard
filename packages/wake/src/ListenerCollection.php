<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Technically\CallableReflection\CallableReflection;

class ListenerCollection
{
    /**
     * @param array<string, callable[]> $listeners Takes the form of: ['EventClass' => [callable, ...], ...]
     */
    public function __construct(
        public array $listeners = [],
    ) {}

    /**
     * Adds a new listener to the collection.
     *
     * @param callable $listener Must accept one typehinted parameter: an event object
     * @throws \InvalidArgumentException if listener validation fails
     */
    public function add(callable $listener): static
    {
        $matched = false;
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            $this->listeners[$paramType][] = $listener;
            $matched = true;
        }
        if (!$matched) {
            throw new \InvalidArgumentException('Listener callable must define one typehinted parameter '
                . 'that accepts an event object');
        }

        return $this;
    }

    /**
     * Remove all listeners for a certain event name.
     */
    public function removeAllForEvent(string $eventName): void
    {
        unset($this->listeners[$eventName]);
    }

    /**
     * Detaches a listener from an event.
     *
     * @param callable $listener The exact listener that was originally attached
     * @param string $eventName The event it is listening for, if different from the parameter's typehint
     */
    public function remove(callable $listener, string $eventName = ''): void
    {
        // Detach from manual event name
        $key = \array_search($listener, $this->listeners[$eventName]);
        if ($key !== false) {
            unset($this->listeners[$eventName][$key]);
            // If there are no more listeners, remove the event
            if (empty($this->listeners[$eventName])) {
                $this->removeAllForEvent($eventName);
            }
            return;
        }

        // Detach from all event class names
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            if (
                !empty($this->listeners[$paramType])
                && ($key = \array_search($listener, $this->listeners[$paramType])) !== false
            ) {
                unset($this->listeners[$paramType][$key]);
            }

            // If there are no more listeners, remove the event
            if (empty($this->listeners[$paramType])) {
                unset($this->listeners[$paramType]);
            }
        }
    }

    /**
     * @return iterable<string>
     */
    protected function getCallableParamTypes(callable $inspect): iterable
    {
        $reflection = CallableReflection::fromCallable($inspect);
        [$param] = $reflection->getParameters();
        foreach ($param->getTypes() as $paramType) {
            if ($paramType->isObject()) {
                yield $paramType->getType();
            }
            if ($paramType->isClassRequirement()) {
                yield $paramType->getClassRequirement();
            }
        }
    }
}
