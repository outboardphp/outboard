<?php

namespace Outboard\Di;

class ExplicitResolver extends AbstractResolver
{
    /**
     * @inheritDoc
     */
    protected function callableAddParams($callable, $definition, $container)
    {
        return $this->addParams($callable, $definition, $container);
    }

    /**
     * @inheritDoc
     */
    protected function constructorAddParams($closure, $id, $definition, $container)
    {
        return $this->addParams($closure, $definition, $container);
    }
}
