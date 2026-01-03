<?php

use Outboard\Wake\ListenerCollection;

/**
 * A default Wake Listener
 *
 * This is the default Wake Listener; other plugins/listeners
 * will override its functionality.
 */
class Formatter
{
    public function listeners(ListenerCollection $collection)
    {
        // Register listeners for stdClass events (or a custom event class if you prefer)
        $collection
            ->add([$this, 'formatUsername'])
            ->add([$this, 'formatGroup'])
            ->add([$this, 'formatDate'])
            ->add([$this, 'formatMessage'])
            ->add([$this, 'onCreatePost']);
    }

    public function formatUsername(object $event): string
    {
        return $event->username ?? '';
    }

    public function formatGroup(object $event): string
    {
        return $event->group ?? '';
    }

    public function formatMessage(object $event): string
    {
        return isset($event->message) ? nl2br($event->message) : '';
    }

    public function formatDate(object $event): string
    {
        return isset($event->date) ? date('Y-m-d H:i:s', $event->date) : '';
    }

    public function onCreatePost(object $event): string
    {
        // Compose the post using the dispatcher to call other formatters
        // (Assume the dispatcher is globally accessible or injected if needed)
        $username = $this->formatUsername($event);
        $group = $this->formatGroup($event);
        $date = $this->formatDate($event);
        $message = $this->formatMessage($event);
        return '<div style="padding: 9px 16px;border:1px solid #EEE;margin-bottom:16px;">'
            . '<strong>Posted by</strong> '
            . $username
            . ' (' . $group . ')<br /><strong>Posted Date</strong> '
            . $date
            . '<br />'
            . $message
            . '</div>';
    }
}
