<?php

use Outboard\Wake\NamedEvent;
use Outboard\Wake\Listener\Collection;

/**
 * An example Wake Listener
 *
 * This is an example Listener/plugin, which will modify
 * previously called listeners. This example Listener enhances
 * the display of posts.
 */
class Fancify
{
    public function listeners(Collection $collection)
    {
        return $collection
            ->add([$this, 'onCreatePost'], 'createPost');
    }

    public function onCreatePost(NamedEvent $event)
    {
        $event->return(str_replace(
            'border:1px solid #EEE;',
            'border:1px solid #DADADA;background:#F1F1F1;font-family:Arial;font-size:15px;',
            $event->return(),
        ));
    }
}
