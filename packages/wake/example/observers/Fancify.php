<?php

use Wake\ListenerCollection;

/**
 * An example Wake Listener
 *
 * This is an example Listener/plugin, which will modify
 * previously called listeners. This example Listener enhances
 * the display of posts.
 */
class Fancify implements \Outboard\Wake\Contracts\Hook
{
    public function listeners(ListenerCollection $collection)
    {
        $collection->add([$this, 'onCreatePost']);
    }

    public function onCreatePost(object $event)
    {
        if (!isset($event->result)) return null;
        return str_replace(
            'border:1px solid #EEE;',
            'border:1px solid #DADADA;background:#F1F1F1;font-family:Arial;font-size:15px;',
            $event->result
        );
    }
}
