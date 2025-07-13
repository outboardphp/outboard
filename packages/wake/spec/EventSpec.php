<?php

namespace spec\Outboard\Wake;

use PhpSpec\ObjectBehavior;
use Outboard\Wake\Event;

class EventSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Event::class);
    }
}
