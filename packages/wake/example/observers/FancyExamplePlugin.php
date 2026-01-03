<?php

use Outboard\Wake\ListenerCollection;

/**
 * An example Wake Listener
 *
 * This is an example Listener/plugin, which will override
 * previously called listeners. This example Listener enhances
 * a post's message.
 */
class FancyExamplePlugin
{
    public function listeners(ListenerCollection $collection)
    {
        $collection->add([$this, 'onFormatMessage']);
    }

    public function onFormatMessage(object $event)
    {
        $message = isset($event->message) ? $event->message : '';
        $message = strip_tags($message);
        $message = preg_replace('/\[b\](.+?)\[\/b\]/is', '<span style="font-weight:bold">$1</span>', $message);
        $message = preg_replace('/\[u\](.+?)\[\/u\]/is', '<span style="text-decoration:underline">$1</span>', $message);
        $message = preg_replace('/\[url=([^\[\]]+)\](.+?)\[\/url\]/is', '<a href="$1">$2</a>', $message);
        $message = preg_replace('/\[url\](.+?)\[\/url\]/is', '<a href="$1">$1</a>', $message);
        return nl2br($message);
    }
}
