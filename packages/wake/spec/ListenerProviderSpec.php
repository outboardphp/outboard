<?php

namespace spec\Outboard\Wake;

use PhpSpec\ObjectBehavior;
use Outboard\Wake\ListenerProvider;

class ListenerProviderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ListenerProvider::class);
    }
}
