<?php

declare(strict_types=1);

namespace Outboard\Wake;

use Psr\EventDispatcher\ListenerProviderInterface;
use Fig\EventDispatcher\TaggedProviderTrait;

/**
 * Mapper from a tagged event to the tagged listeners that are applicable to that event.
 * Listeners can opt to handle all tags by using the tag '*'.
 *
 * @see https://www.php-fig.org/psr/psr-14/
 */
class TaggedListenerProvider implements ListenerProviderInterface
{
    use TaggedProviderTrait;

    public function __construct(
        public readonly TaggedListenerCollection $listeners,
        protected string $eventType = 'stdClass',
        protected string $tagMethod = 'tag'
    ) {}

    protected function eventType(): string
    {
        return $this->eventType;
    }

    protected function tagMethod(): string
    {
        return $this->tagMethod;
    }

    protected function getListenersForTag(string $tag): iterable
    {
        return $this->listeners->getListenersForTag($tag);
    }

    protected function getListenersForAllTags(): iterable
    {
        return $this->getListenersForTag('*');
    }
}
