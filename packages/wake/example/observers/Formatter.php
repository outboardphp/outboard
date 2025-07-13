<?php

use Outboard\Wake\Dispatcher;
use Outboard\Wake\NamedEvent;
use Outboard\Wake\Listener\Collection;

/**
 * A default Wake Listener
 *
 * This is the default Wake Listener; other plugins/listeners
 * will override its functionality.
 */
class Formatter
{
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    public function listeners(Collection $collection)
    {
        return $collection
            ->add([$this, 'formatUsername'], 'formatUsername')
            ->add([$this, 'formatGroup'], 'formatGroup')
            ->add([$this, 'formatDate'], 'formatDate')
            ->add([$this, 'formatMessage'], 'formatMessage')
            ->add([$this, 'onCreatePost'], 'createPost');
    }

    public function formatUsername(NamedEvent $event)
    {
        $event->return($event->data(0));
    }

    public function formatGroup(NamedEvent $event)
    {
        $event->return($event->data(0));
    }

    public function formatMessage(NamedEvent $event)
    {
        $event->return(nl2br($event->data(0)));
    }

    public function formatDate(NamedEvent $event)
    {
        $event->return($event->data(0));
    }

    public function onCreatePost(NamedEvent $event)
    {
        $event->return('<div style="padding: 9px 16px;border:1px solid #EEE;margin-bottom:16px;">'
             . '<strong>Posted by</strong> '
             . $this->dispatcher->dispatch(new NamedEvent('formatUsername', [$event->data('username')], $this))->return()
             . ' ('
             . $this->dispatcher->dispatch(new NamedEvent('formatGroup', [$event->data('group')], $this))->return()
             . ')<br /><strong>Posted Date</strong> '
             . $this->dispatcher->dispatch(new NamedEvent('formatDate', [$event->data('date')], $this))->return()
             . '<br />'
             . $this->dispatcher->dispatch(new NamedEvent('formatMessage', [$event->data('message')], $this))->return()
             . '</div>');
    }
}
