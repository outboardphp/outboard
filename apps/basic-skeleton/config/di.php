<?php

use Outboard\Di\ValueObjects\Definition;

return [
    Outboard\Framework\Application::class => new Definition(),
    Outboard\Framework\ConfigProvider::class => new Definition(),
    App\ConfigProvider::class => new Definition(),
];
