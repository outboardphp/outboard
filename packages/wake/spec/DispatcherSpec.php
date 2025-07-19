<?php

namespace spec\Outboard\Wake;

use PhpSpec\ObjectBehavior;
use Psr\EventDispatcher\ListenerProviderInterface;
use Outboard\Wake\Dispatcher;
use Outboard\Wake\Event;
use Outboard\Wake\NamedEvent;

class DispatcherSpec extends ObjectBehavior
{
    public function let($lpi)
    {
        $lpi->beADoubleOf(ListenerProviderInterface::class);
        $this->beConstructedWith($lpi);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Dispatcher::class);
    }

    public function it_will_not_hold_unheard_events_by_default()
    {
        $this->holdUnheardEvents()->shouldBe(false);
    }

    public function it_may_hold_unheard_events()
    {
        $this->holdUnheardEvents(true);

        $this->holdUnheardEvents()->shouldBe(true);
    }

    public function it_clears_the_pending_list()
    {
        $this->holdUnheardEvents(true);

        $this->dispatch(new Event());
        $this->holdUnheardEvents(false);

        $this->held->shouldEqual([]);
    }

    public function it_holds_the_pending_event()
    {
        $this->holdUnheardEvents(true);

        $this->dispatch(new NamedEvent('randomname'));

        $this->held[0]->shouldBeAnInstanceOf('Outboard\Wake\NamedEvent');
        $this->held[0]->name->shouldEqual('randomname');
    }

    public function it_has_no_subscribers()
    {
        $this->shouldNotHaveSubscribers('randomname');
    }

    public function it_can_register_basic_handlers()
    {
        $eventname = 'randomname';
        $callback = static function () {};

        $this->subscribe([$eventname => [$callback]]);

        $this->shouldHaveSubscribers($eventname);
        $this->isSubscribed($eventname, $callback)->shouldBeInteger();
    }

    public function it_can_register_time_handlers()
    {
        $eventname = 'timer';
        $callback = static function () {};

        $this->subscribe([$eventname . ':10' => [$callback]]);

        $this->shouldHaveSubscribers($eventname);
        $this->isSubscribed($eventname, $callback)->shouldBeInteger();
    }

    public function it_can_unregister_all_handlers()
    {
        $eventname = 'randomname';
        $callback = static function () {};
        $this->subscribe([$eventname => [$callback]]);
        $this->subscribe([$eventname => [$callback]]);

        $this->unsubscribe([$eventname => '*']);

        $this->shouldNotHaveSubscribers($eventname);
    }

    public function it_can_unregister_specific_handlers()
    {
        $eventname = 'randomname';
        $callback1 = static function () { return true; };
        $callback2 = static function () { return false; };
        $this->subscribe([$eventname => [$callback1]]);
        $this->subscribe([$eventname => [$callback2]]);

        $this->unsubscribe([$eventname => $callback1]);

        $this->isSubscribed($eventname, $callback2)->shouldBeInteger();
        $this->isSubscribed($eventname, $callback1)->shouldReturn(false);
    }

    public function it_can_publish_events()
    {
        $eventname = 'randomname';
        $callback = static function () { return 'event handled'; };
        $this->subscribe([$eventname => [$callback]]);

        $this->publish(new NamedEvent($eventname))->shouldReturn('event handled');
    }

    public function it_can_handle_timed_events()
    {
        $eventname = 'timer';
        $callback = static function () { return 'timed event handled'; };
        $this->subscribe([$eventname . ':100' => [$callback]]);
        $timer = new Event('timer');

        while (($result = $this->getWrappedObject()->publish($timer)) === null) {
        }

        if ($result != 'timed event handled') {
            throw new \Exception("bad result: {$result}");
        }
    }
}
