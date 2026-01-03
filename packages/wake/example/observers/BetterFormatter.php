<?php

use Outboard\Wake\ListenerCollection;

/**
 * An example Wake Listener
 *
 * This is an example Listener/plugin, which will override
 * previously called Listeners. This example Listener enhances
 * the group and date formatting.
 */
class BetterFormatter
{
    public function listeners(ListenerCollection $collection)
    {
        $collection
            ->add([$this, 'onFormatGroup'])
            ->add([$this, 'onFormatDate']);
    }

    public function onFormatGroup(object $event)
    {
        $groupName = strtolower($event->group ?? '');

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

        return $groupName;
    }

    public function onFormatDate(object $event)
    {
        return isset($event->date) ? date('F j, Y h:i:s A T', $event->date) : '';
    }
}
