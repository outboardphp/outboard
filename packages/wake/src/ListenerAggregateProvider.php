<?php

namespace Outboard\Wake;

/**
 * @inheritDoc
 *
 * This proxy class is provided to simplify its usage. Rather than
 * explicitly adding a dependency on the parent class with or without listing
 * its package in composer.json, the dependency can be directly on this class
 * since this library would be explicitly included in composer.json -- plus,
 * this library already depends on fig/event-dispatcher-util anyway, so
 * it's not like leaving this out would save a dependency if a user doesn't
 * need it. Plus, depending on this class means you'll get any updates I make
 * if I decide to change how something is handled.
 */
class ListenerAggregateProvider extends \Fig\EventDispatcher\AggregateProvider {}
