<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Contracts\DefinitionProvider;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\ValueObjects\Definition;

class CombinedDefinitionProvider implements DefinitionProvider
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
        $result = [];
        foreach ($definitionSets as $set) {
            foreach ($set as $id => $definition) {
                set_error_handler(static function () { return true; });
                try {
                    $isRegex = preg_match($id, '') !== false;
                } catch (\Throwable) {
                    $isRegex = false;
                } finally {
                    restore_error_handler();
                }
                if (!$isRegex) {
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
