<?php

namespace spec\Outboard\Wake;

use PhpSpec\ObjectBehavior;
use Outboard\Wake\NamedEvent;

class NamedEventSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(NamedEvent::class);
    }
}
