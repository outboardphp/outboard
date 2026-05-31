<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contract\DefinitionProvider;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\ValueObject\Definition;

class OverrideableDefinitionProvider implements DefinitionProvider
{
    /**
     * @param string[] $overrideableDefinitionIds
     */
    public function __construct(
        protected DefinitionProvider $frameworkProvider,
        protected DefinitionProvider $appProvider,
        protected array $overrideableDefinitionIds,
        protected DefinitionIdNormalizer $definitionIdNormalizer = new DefinitionIdNormalizer(),
    ) {
    }

    /**
     * @throws ContainerException
     * @return array<string, Definition>
     */
    public function getDefinitions(): array
    {
        $frameworkDefinitions = $this->normalizeDefinitions($this->frameworkProvider->getDefinitions());
        $appDefinitions = $this->normalizeDefinitions($this->appProvider->getDefinitions());
        $overrideableIds = $this->normalizeIds($this->overrideableDefinitionIds);

        foreach ($appDefinitions as $id => $definition) {
            if (isset($frameworkDefinitions[$id]) && !isset($overrideableIds[$id])) {
                throw new ContainerException("Definition collision: {$id} cannot be overridden.");
            }

            $frameworkDefinitions[$id] = $definition;
        }

        return $frameworkDefinitions;
    }

    /**
     * @param array<string, Definition> $definitions
     * @return array<string, Definition>
     */
    protected function normalizeDefinitions(array $definitions): array
    {
        $normalized = [];

        foreach ($definitions as $id => $definition) {
            $normalized[$this->definitionIdNormalizer->normalizeDefinitionId($id)] = $definition;
        }

        return $normalized;
    }

    /**
     * @param string[] $ids
     * @return array<string, true>
     */
    protected function normalizeIds(array $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            $normalized[$this->definitionIdNormalizer->normalizeDefinitionId($id)] = true;
        }

        return $normalized;
    }
}
