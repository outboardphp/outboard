<?php

declare(strict_types=1);

namespace Outboard\Framework;

use Psr\Container\ContainerInterface;

class Application
{
    public function __construct(protected ContainerInterface $container) {}
    public function __invoke(): void
    {
        // Here we would typically bootstrap the application, set up the DI container, and run the application.
        // For now, let's just return a simple message.
        echo "Application has been invoked!";
    }
}
