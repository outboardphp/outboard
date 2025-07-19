<?php

use Outboard\Wake\NamedEvent;
use Outboard\Wake\Listener\Collection;

/**
 * An example Wake Listener
 *
 * This is an example Listener/plugin, which will override
 * previously called Listeners. This example Listener enhances
 * the group and date formatting.
 */
class BetterFormatter
{
    public function listeners(Collection $collection)
    {
        return $collection
            ->add([$this, 'onFormatGroup'], 'formatGroup')
            ->add([$this, 'onFormatDate'], 'formatDate');
    }

    public function onFormatGroup(NamedEvent $event)
    {
        $groupName = strtolower($event->data(0));

        switch ($groupName) {
            case 'admin':
            case 'administrator':
                $groupName = '<span style="color:#F00;">Administrator</span>';
                break;

            case 'mod':
            case 'moderator':
                $groupName = '<span style="color:#00A;">Moderator</span>';
                break;
        }

        $event->return($groupName);
    }

    public function onFormatDate(NamedEvent $event)
    {
        $event->return(date('F j, Y h:i:s A T', $event->data(0)));
    }
}
