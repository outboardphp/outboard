<?php

use Outboard\Di\ValueObject\Definition;

return [
    \Outboard\Framework\Application::class => new Definition(),
    \Outboard\Framework\ConfigProvider::class => new Definition(),
    \App\ConfigProvider::class => new Definition(),
];
