<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\DefinitionProvider;
use Outboard\Di\Exception\ContainerException;

class AggregateDefinitionProvider implements DefinitionProvider
{
    use Traits\NormalizesId; // in combine()

    /**
     * @var array<string, Definition>
     */
    protected array $definitions;

    /**
     * @param DefinitionProvider[] $providers
     */
    public function __construct(protected array $providers) {}

    /**
     * @return array<string, Definition>
     * @throws ContainerException
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
     * @return array<string, Definition>
     * @throws ContainerException
     */
    protected function combine($definitionSets)
    {
        // This was extracted to its own function in anticipation that the strategy will likely change
        $result = [];
        foreach ($definitionSets as $set) {
            foreach ($set as $id => $definition) {
                if (preg_match($id, '') === false) {
                    // $id is not a valid regex, so treat it as a class identifier.
                    // Yes, this means arbitrary strings will also be normalized to lowercase.
                    $id = static::normalizeId($id);
                }

                if (isset($result[$id])) {
                    throw new ContainerException("Definition collision: $id is already defined");
                }
                $result[$id] = $definition;
            }
        }
        return $result;
    }
}
