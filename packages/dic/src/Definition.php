<?php

declare(strict_types=1);

namespace Outboard\Di;

class Definition
{
    /**
     * @param bool|string $singleton Whether this class instance should be unique in the container.
     *   String values supported are: 'prototype' (same as false), 'singleton' (same as true),
     *   'request' (scoped to current request), 'session' (scoped to current session);
     *   the latter two are mostly only useful in long-running application contexts.
     * @param bool $strict Whether to prevent this rule from applying to child classes.
     * @param string|callable|object|null $substitute The FQCN of the actual class to instantiate,
     *  a factory callable to generate the instance, or a pre-existing instance.
     * @param array $withParams Parameters to pass to the constructor of the class.
     * @param array $singletonsInTree Classes whose instances are singletons only within the current object graph.
     * @param ?callable $call Method to call on the instance after construction. If an object is returned, this is
     *  considered to be a decorator and the instance will be replaced with the return value.
     * @param array $tags An array of tags for service tagging.
     */
    public function __construct(
        public bool|string $singleton = false,
        public bool $strict = false,
        public $substitute = null,
        public array $withParams = [],
        public array $singletonsInTree = [],
        public $call = null,
        public array $tags = [],
    ) {}
}
