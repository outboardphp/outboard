<?php

declare(strict_types=1);

namespace Outboard\Framework;

class ConfigProvider implements \Outboard\Di\Contracts\DefinitionProvider
{
    public function getDefinitions(): array
    {
        // TODO: Implement getDefinitions() method.
        // The most basic things we need are a DI container object, its configuration, and an invokable Application object.
        // We may want a Builder class to provide the DI config in order to simplify it, because I want to keep the
        // essential bits of config out of the app skeleton.
        // Configs we need to provide inside the framework:
        // - DI container
        // - Router
        // - PSR-17 factory
        // - PSR-15 middleware pieces
        // - PSR-7 request and response objects
        // - PSR-14 event dispatcher
        //
        // Configs the user needs to provide: (we should suggest)
        // - DB connections
        // - Routes
        // - Templating engine
        // - ORM
        // - Logger
    }
}
