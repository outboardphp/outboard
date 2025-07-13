<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\DefinitionProvider;

class AggregateDefinitionProvider implements DefinitionProvider
{
    /**
     * @var DefinitionProvider[]
     */
    private array $providers;

    /**
     * @param DefinitionProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function getDefinitions(): array
    {
        $allDefinitions = [];
        foreach ($this->providers as $provider) {
            $allDefinitions[] = $provider->getDefinitions();
        }
        return array_merge(...$allDefinitions);
    }
}
