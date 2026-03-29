<?php

namespace Outboard\Di\Contract;

use Psr\Container\ContainerInterface;

interface ComposableContainer extends ContainerInterface
{
    public function setParent(ContainerInterface $container): void;
}
