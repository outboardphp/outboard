<?php

namespace Outboard\Di\ValueObjects;

use Outboard\Di\Enums\Scope;

readonly class Definition
{
    /**
     * Note: The contents of each definition determine whether the resulting configuration can be compiled or not.
     * If the definition is not compilable, it will be resolved at runtime and thus incur a performance penalty.
     * Specifically, `substitute` must be a container id, FQCN, or a non-Closure callable if provided;
     * and $call must be a non-Closure callable if provided.
     *
     * @param bool|Scope $singleton Whether this class instance should be unique in the container.
     *   Scope values supported are: Prototype (same as false), Singleton (same as true),
     *   Request (scoped to current request), Session (scoped to current session);
     *   the latter two are mostly only useful in long-running application contexts.
     * @param bool $strict Whether to prevent this rule from applying to child classes.
     * @param string|callable|object|null $substitute The FQCN of the actual class to instantiate,
     *  a factory callable to generate the instance, or a pre-existing instance.
     * @param mixed[] $withParams Parameters to pass to the constructor of the class.
     * @param string[] $singletonsInTree Classes whose instances are singletons only within the current object graph.
     * @param ?callable $call Method to call on the instance after construction. If an object is returned, this is
     *  considered to be a decorator and the instance will be replaced with the return value.
     * @param string[] $tags An array of tags for service tagging.
     */
    public function __construct(
        public bool|Scope $singleton = false,
        public bool $strict = false,
        public mixed $substitute = null,
        public array $withParams = [],
        public array $singletonsInTree = [],
        public mixed $call = null,
        public array $tags = [],
    ) {}
}
