<?php

define('BASEDIR', __DIR__);

require_once BASEDIR . '/../vendor/autoload.php';

use Outboard\Wake\EventDispatcher;
use Outboard\Wake\ListenerCollection;
use Outboard\Wake\ListenerProvider;

// Include the listeners
include BASEDIR . '/observers/Formatter.php';
include BASEDIR . '/observers/FancyExamplePlugin.php';
include BASEDIR . '/observers/BetterFormatter.php';
include BASEDIR . '/observers/Fancify.php';

// Set up the listener collection and dispatcher
$listeners = new ListenerCollection();
$dispatcher = new EventDispatcher(new ListenerProvider($listeners));

// Initialize the default application listeners
$formatter = new Formatter();
$formatter->listeners($listeners);
// Initialize plugin listeners--assigned to vars so we can mess with them later
$fancyExamplePlugin = new FancyExamplePlugin();
$fancyExamplePlugin->listeners($listeners);
$betterFormatter = new BetterFormatter();
$betterFormatter->listeners($listeners);
$fancify = new Fancify();
$fancify->listeners($listeners);

$sampleMessage = <<<HTML
Lorem [b]ipsum dolor sit amet[/b], consectetur adipiscing elit. Fusce dignissim neque vitae velit mollis, ac volutpat mauris consequat. Morbi sed arcu leo. Vestibulum dignissim, est at blandit suscipit, sapien leo [u]iaculis massa, mollis faucibus[/u] odio mauris sed risus. Integer mollis, ipsum ut efficitur lobortis, ex enim dictum felis, in mattis purus orci [b]in nulla. Nunc [u]semper mauris[/u] enim[/b], quis faucibus massa luctus quis. Sed ut malesuada magna, cursus ullamcorper augue. Curabitur orci nisl, mattis quis elementum eu, condimentum at lorem. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aliquam ultricies tristique urna in maximus. Praesent facilisis, [url=http://github.com/DavidRockin]diam ac euismod sollicitudin[/url], eros diam consectetur est, quis egestas nisl orci vel nisl. Aenean consectetur justo non felis varius, eu fermentum mi fermentum. Ut ac dui ligula.
For more information please visit [url]http://github.com/garrettw[/url]

HTML;

function createPostEvent($username, $group, $date, $message) {
    $event = new stdClass();
    $event->username = $username;
    $event->group = $group;
    $event->date = $date;
    $event->message = $message;
    return $event;
}

echo "With better formatting\n",
    $dispatcher->dispatch(createPostEvent('Garrett', 'Administrator', time(), $sampleMessage))->result ?? '', "\n",
    $dispatcher->dispatch(createPostEvent('John Doe', 'Moderator', strtotime('-3 days'), $sampleMessage))->result ?? '';

// Usually this should be handled by custom public methods in the observers,
// because this code wouldn't be aware of the exact subscription
// $listeners->detach([$betterFormatter, 'onFormatGroup'], 'formatGroup');
// $listeners->detach([$fancify, 'onCreatePost'], 'createPost');
// $fancify->unsubscribe();

echo "\n\nWithout the better formatting on group and post\n",
    $dispatcher->dispatch(createPostEvent('AppleJuice', 'Member', strtotime('-3 weeks'), $sampleMessage))->result ?? '', "\n",
    $dispatcher->dispatch(createPostEvent('Anonymous', 'Donator', strtotime('-3 years'), $sampleMessage))->result ?? '';

// $listeners->detach([$fancyExamplePlugin, 'onFormatMessage'], 'formatMessage');
// $fancyExamplePlugin->unsubscribe();

echo "\n\nAfter destroying the fancyExamplePlugin listener\n",
    $dispatcher->dispatch(createPostEvent('AppleJuice', 'Member', strtotime('-3 weeks'), $sampleMessage))->result ?? '';
