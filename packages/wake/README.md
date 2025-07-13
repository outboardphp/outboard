# Wake

Outboard Wake is a full-featured, [PSR-14](http://www.php-fig.org/psr/psr-14/)-compliant event library for PHP, combining the Mediator and Observer patterns.
It includes an event dispatcher and a basic listener provider, as per the spec, and adds listener collections and
other variations on listener providers to meet special needs.

The goal of this library is to meet as many needs as possible with code that is as generic and flexible as possible. \
Here are two examples where I take a different approach than that of [Tukio](https://github.com/crell/tukio):

1. Logging
   - Tukio lets you pass a PSR-3 logger as a second constructor parameter when creating a dispatcher.
     In short, this means the dispatcher has special awareness of the logger and handles it in a unique way.
   - Wake's dispatcher doesn't treat loggers differently than anything else. In fact, it's up to you to create
     listeners that interact with a logger based on your needs.
     - For example, if you want to log all events, you would create a listener that typehints `stdClass`, call the logger inside, and pass that listener to the 
       dispatcher.
     - If you wanted to log exceptions, same story: just create a listener that typehints `\Exception` instead.
     - Now if you wanted to *avoid* Exceptions in the generic event logger, you could just add a sniff in the body of the listener to ignore that type.
2. Dispatcher
   - Tukio provides a special dispatcher for debugging which first logs each event and then hands off to the actual dispatcher to process the event.
   - As mentioned above, Wake's standard dispatcher is perfectly happy to accept a listener that is type-hinted to match all objects; it is trivial to create a listener accordingly that will log all events.

Currently in the middle of a major refactoring so documentation and tests are incomplete.

### Inspired by
- https://github.com/DavidRockin/Podiya
- https://github.com/yiisoft/event-dispatcher
- https://event.thephpleague.com/

### Planned features
- Event/listener ordering (priority, before/after)
- Consider dispatcher behavior for error tracking/debugging listeners; maybe put ones that typehint stdClass or Exception at the highest priority?
- Support async event processing via ListenerProviders that interface with MQs/DBs

### Future inspiration to be taken from:
- https://laravel.com/docs/11.x/events (queues?)
- https://github.com/crell/tukio
   - https://hive.blog/php/@crell/psr-14-being-a-good-provider
- https://github.com/pleets/php-event-dispatcher (if serialization is needed)
- https://github.com/Superbalist/php-pubsub (drivers/adapters)
- https://github.com/phly/phly-event-dispatcher (MWOP)

## Install
```bash
composer require outboardphp/wake
```

## Basic Usage
```php
use Wake\ListenerCollection;
use Wake\ListenerProvider;
use Wake\EventDispatcher;

$listeners = (new ListenerCollection())
    ->add(function (stdClass $event) {
        // This anonymous function is an event "listener". It listens for any
        // events being fired that match the typehint given on the parameter.
        // Here I'm having it handle any object that was fired as an event.
        // (The typehint to do that has to be 'stdClass' and not 'object',
        // because it has to match the classname of the object.)
        // You can also use union types to target more than one event class.
        // You would replace these comment lines with code that does
        // something in response to the event being fired.
    })->add(
        // You can daisy-chain add() calls because it returns the
        // collection object each time.
    );

// Now we have 1 listener in the collection, ready to use, so we initialize
// a ListenerProvider with our collection, and then give that to the Dispatcher.
$dispatcher = new EventDispatcher(new ListenerProvider($listeners));
// and now our dispatcher is set up and ready as well.

// Let's create a very basic type of event object that the listener above will handle:
$event = new stdClass;
$event->data = 'some string';

// And now we'll fire it off and the registered listener(s) will handle it immediately.
// You can use the PSR-compliant method call like so:
$dispatcher->dispatch($event);
// ... or you can just invoke it directly, like this:
// $dispatcher($event);
```

## Creating Event Classes
Wake provides a few interfaces and traits you may want to use when writing
event classes.

### Stopping Event Propagation
The most basic mechanism to allow an event to stop propagating to other listeners
involves implementing `Psr\EventDispatcher\StoppableEventInterface`, which
consists of a single method:
```php
public function isPropagationStopped(): bool;
```
You can then implement that method yourself with any logic you want, or you can
include this line in your class:
```php
    use \Wake\Traits\CanStopPropagation;
```
and control the behavior elsewhere within the class by simply setting
`$this->stopPropagation` to `true`.

#### External Control
On the other hand, if you want to also allow code outside of the event to control its
propagation, have your event class implement `Wake\Contracts\ForceStop` with the
following trait:
```php
    use \Wake\Traits\CanBeStopped;
```
This adds a method called `stopPropagation()` to your event class.

### Direction of Data Flow
Events largely represent unidirectional data flow; an event is fired, and
listeners receive it. But since events can be any object and thus contain
arbitrary behavior, this library does not make any assumptions about how
events behave.

In order to better support the use case of bidirectional data flow,
Wake includes a specific interface/trait combination to help you build
bidirectional event classes, which we call Hooks.
- Interface: `Wake\Contracts\Hook`
- Trait: `Wake\Traits\Hook`

The Wake Dispatcher is aware of the `Hook` interface, so when it encounters
one, it will store the return value of the listener(s) it calls back into the
event object. So after all listeners are called for a given Hook, and the
Dispatcher returns the event object, you can just call `getResult()` on the
event to retrieve the value returned by the last listener -- or if you want
an array of all listeners' return values, just call `getAllResults()` which
will return an array.
