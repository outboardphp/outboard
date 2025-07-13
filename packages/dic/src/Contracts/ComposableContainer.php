<?php

namespace Outboard\Di\Contracts;

use Psr\Container\ContainerInterface;

interface ComposableContainer extends ContainerInterface
{
    public function setParent(ContainerInterface $container): void;
}
