<?php

namespace Outboard\Di;

use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\ValueObject\Definition;

class CombinedDefinitionProvider implements DefinitionProvider
{
    /** @var array<string, Definition> */
    protected array $definitions;

    /**
     * @param DefinitionProvider[] $providers
     */
    public function __construct(
        protected array $providers,
        protected DefinitionIdNormalizer $definitionIdNormalizer = new DefinitionIdNormalizer(),
    ) {
    }

    /**
     * @throws ContainerException
     * @return array<string, Definition>
     */
    public function getDefinitions(): array
    {
        if (!isset($this->definitions)) {
            // lazy load
            $definitionSets = \array_map(
                static fn(DefinitionProvider $provider) => $provider->getDefinitions(),
                $this->providers,
            );
            $this->definitions = $this->combine($definitionSets);
        }
        return $this->definitions;
    }

    /**
     * @param array<int, array<string, Definition>> $definitionSets
     * @throws ContainerException
     * @return array<string, Definition>
     */
    protected function combine($definitionSets)
    {
        $result = [];
        foreach ($definitionSets as $set) {
            foreach ($set as $id => $definition) {
                $id = $this->definitionIdNormalizer->normalizeDefinitionId($id);
                if (isset($result[$id])) {
                    throw new ContainerException("Definition collision: {$id} is already defined");
                }
                $result[$id] = $definition;
            }
        }
        return $result;
    }
}
