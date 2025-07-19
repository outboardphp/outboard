<?php

namespace spec\Outboard\Wake;

use PhpSpec\ObjectBehavior;

class ObserverSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beAnInstanceOf('spec\Outboard\Wake\ObserverExample');
        $this->beConstructedWith(new \Outboard\Wake\Mediator(new \Outboard\Wake\Manager()));
        $this->subscribe();
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf('Outboard\Wake\Observer');
        $this->shouldBeAnInstanceOf('spec\Outboard\Wake\ObserverExample');
    }

    public function it_keeps_an_instance_of_mediator()
    {
        $this->mediator->shouldBeAnInstanceOf('Outboard\Wake\Mediator');
    }

    public function it_responds_to_all_events()
    {
        $this->mediator->publish(new \Outboard\Wake\Event('random'))->shouldReturn('random');
    }
}

class ObserverExample extends \Outboard\Wake\Observer
{
    public function subscribe($newhandler = null)
    {
        // This is just here for an example of explicitly-defined handlers
        $this->handlers = [
            'all' => [[$this, 'log'], \Outboard\Wake\Manager::PRIORITY_URGENT, true],
            'one' => [[$this, 'handlerOne']],
        ];
        parent::subscribe($newhandler);
    }

    public function handlerOne(\Outboard\Wake\Event $e)
    {
        return 'one';
    }

    public function onTwo(\Outboard\Wake\Event $e)
    {
        return 'two';
    }

    public function log(\Outboard\Wake\Event $e)
    {
        return $e->name;
    }
}
