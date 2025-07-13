<?php

use Outboard\Wake\NamedEvent;
use Outboard\Wake\Listener\Collection;

/**
 * An example Wake Listener
 *
 * This is an example Listener/plugin, which will override
 * previously called listeners. This example Listener enhances
 * a post's message.
 */
class FancyExamplePlugin
{
    public function listeners(Collection $collection)
    {
        return $collection
            ->add([$this, 'onFormatMessage'], 'formatMessage');
    }

    public function onFormatMessage(NamedEvent $event)
    {
        $message = strip_tags($event->data(0));
        $message = preg_replace('/\[b\](.+?)\[\/b\]/is', '<span style="font-weight:bold">$1</span>', $message);
        $message = preg_replace('/\[u\](.+?)\[\/u\]/is', '<span style="text-decoration:underline">$1</span>', $message);
        $message = preg_replace('/\[url=([^\[\]]+)\](.+?)\[\/url\]/is', '<a href="$1">$2</a>', $message);
        $message = preg_replace('/\[url\](.+?)\[\/url\]/is', '<a href="$1">$1</a>', $message);
        $event->return(nl2br($message));
    }
}
