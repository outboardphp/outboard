<?php

define('BASEDIR', dirname(__FILE__));

require_once BASEDIR . '/../vendor/autoload.php';

use Outboard\Wake\Dispatcher;
use Outboard\Wake\Listener\Collection;
use Outboard\Wake\Listener\Provider;
use Outboard\Wake\NamedEvent;

// Include the listeners
include BASEDIR . '/observers/Formatter.php';
include BASEDIR . '/observers/FancyExamplePlugin.php';
include BASEDIR . '/observers/BetterFormatter.php';
include BASEDIR . '/observers/Fancify.php';

// Set up the dispatcher and collection
$listeners = new Collection();
$hub = new Dispatcher(new Provider($listeners));

// Initialize the default application listeners
$formatter = new Formatter($hub);
$formatter->listeners($listeners);
// Initialize plugin listeners--assigned to vars so we can mess with them later
$fancyExamplePlugin = new FancyExamplePlugin();
$betterFormatter = new BetterFormatter();
$fancify = new Fancify();
$fancyExamplePlugin->listeners($listeners);
$betterFormatter->listeners($listeners);
$fancify->listeners($listeners);


$sampleMessage = <<<HTML
Lorem [b]ipsum dolor sit amet[/b], consectetur adipiscing elit. Fusce dignissim neque vitae velit mollis, ac volutpat mauris consequat. Morbi sed arcu leo. Vestibulum dignissim, est at blandit suscipit, sapien leo [u]iaculis massa, mollis faucibus[/u] odio mauris sed risus. Integer mollis, ipsum ut efficitur lobortis, ex enim dictum felis, in mattis purus orci [b]in nulla. Nunc [u]semper mauris[/u] enim[/b], quis faucibus massa luctus quis. Sed ut malesuada magna, cursus ullamcorper augue. Curabitur orci nisl, mattis quis elementum eu, condimentum at lorem. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aliquam ultricies tristique urna in maximus. Praesent facilisis, [url=http://github.com/DavidRockin]diam ac euismod sollicitudin[/url], eros diam consectetur est, quis egestas nisl orci vel nisl. Aenean consectetur justo non felis varius, eu fermentum mi fermentum. Ut ac dui ligula.
For more information please visit [url]http://github.com/garrettw[/url]

HTML;


echo "With better formatting\n",
    $hub->dispatch(new NamedEvent('createPost', [
        'username' => 'Garrett',
        'group'    => 'Administrator',
        'date'     => time(),
        'message'  => $sampleMessage,
    ]))->return(), "\n",
    $hub->dispatch(new NamedEvent('createPost', [
        'username' => 'John Doe',
        'group'    => 'Moderator',
        'date'     => strtotime('-3 days'),
        'message'  => $sampleMessage,
    ]))->return();

// Usually this should be handled by custom public methods in the observers,
// because this code wouldn't be aware of the exact subscription
$listeners->detach([$betterFormatter, 'onFormatGroup'], 'formatGroup');

$listeners->detach([$fancify, 'onCreatePost'], 'createPost');
// $fancify->unsubscribe();

echo "\n\nWithout the better formatting on group and post\n",
    $hub->dispatch(new NamedEvent('createPost', [
        'username' => 'AppleJuice',
        'group'    => 'Member',
        'date'     => strtotime('-3 weeks'),
        'message'  => $sampleMessage,
    ]))->return(), "\n",
    $hub->dispatch(new NamedEvent('createPost', [
        'username' => 'Anonymous',
        'group'    => 'Donator',
        'date'     => strtotime('-3 years'),
        'message'  => $sampleMessage,
    ]))->return();

$listeners->detach([$fancyExamplePlugin, 'onFormatMessage'], 'formatMessage');
// $fancyExamplePlugin->unsubscribe();

echo "\n\nAfter destroying the fancyExamplePlugin listener\n",
    $hub->dispatch(new NamedEvent('createPost', [
        'username' => 'AppleJuice',
        'group'    => 'Member',
        'date'     => strtotime('-3 weeks'),
        'message'  => $sampleMessage,
    ]))->return();
