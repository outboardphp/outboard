<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Technically\CallableReflection\CallableReflection;

class TaggedListenerCollection
{
    /**
     * @param array<string, array<string, callable[]>> $listeners Takes the form of: ['tagName' => ['EventClass' => [callable, ...], ...], ...]
     */
    public function __construct(
        private array $listeners = [],
    ) {}

    /**
     * Get listeners for the tag name specified.
     *
     * @return array<string, callable[]> listeners
     */
    public function getListenersForTag(string $tag): array
    {
        return $this->listeners[$tag] ?? [];
    }

    /**
     * Adds a new listener to a given tag along with the event(s) it listens for.
     *
     * @param callable $listener Must accept one typehinted parameter: an event object
     * @throws \InvalidArgumentException if listener validation fails
     */
    public function add(string $tag, callable $listener): static
    {
        $matched = false;
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            $this->listeners[$tag][$paramType][] = $listener;
            $matched = true;
        }
        if (!$matched) {
            throw new \InvalidArgumentException('Listener callable must define one typehinted parameter '
                . 'that accepts an event object');
        }

        return $this;
    }

    /**
     * Remove all listeners for a certain tag or an event name under a certain tag.
     */
    public function removeAllForTag(string $tag, string $eventName = ''): void
    {
        if (empty($eventName)) {
            unset($this->listeners[$tag]);
        } else {
            unset($this->listeners[$tag][$eventName]);
        }
    }

    /**
     * Detaches a listener from an event.
     *
     * @param callable $listener The exact listener that was originally attached
     * @param string $eventName The event it is listening for, if different from the parameter's typehint
     */
    public function remove(string $tag, callable $listener, string $eventName = ''): void
    {
        // Detach from manual event name
        $key = \array_search($listener, $this->listeners[$tag][$eventName]);
        if ($key !== false) {
            unset($this->listeners[$tag][$eventName][$key]);
            // If there are no more listeners, remove the event
            if (empty($this->listeners[$tag][$eventName])) {
                $this->removeAllForTag($tag, $eventName);
            }
            return;
        }

        // Detach from all event class names
        foreach ($this->getCallableParamTypes($listener) as $paramType) {
            if (
                !empty($this->listeners[$tag][$paramType])
                && ($key = \array_search($listener, $this->listeners[$tag][$paramType])) !== false
            ) {
                unset($this->listeners[$tag][$paramType][$key]);
            }

            // If there are no more listeners, remove the event
            if (empty($this->listeners[$tag][$paramType])) {
                unset($this->listeners[$tag][$paramType]);
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
