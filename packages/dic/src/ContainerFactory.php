<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\DefinitionProvider;
use Outboard\Di\Contracts\Resolver;
use Psr\Container\ContainerExceptionInterface;

class ContainerFactory
{
    /**
     * @param class-string<Resolver>[] $resolvers
     */
    public function __construct(
        protected ?DefinitionProvider $definitionProvider = null,
        protected array $resolvers = [
            ExplicitResolver::class,
            // AutowiringResolver::class, // disabled until it's working
        ],
    ) {}

    /**
     * @throws ContainerExceptionInterface
     */
    public function __invoke(): Container
    {
        $defs = $this->definitionProvider?->getDefinitions() ?? [];
        $resolvers = \array_map(
            static fn(string $resolverClass) => new $resolverClass($defs),
            $this->resolvers,
        );
        $this->validateConfig(\array_keys($defs), $resolvers);
        return new Container($resolvers);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function build(): Container
    {
        return $this();
    }

    /**
     * Validates the configuration of the container by ensuring that all
     * definitions can be resolved without actually
     * constructing any instances.
     * This is useful to catch circular dependencies
     * or missing definitions before the container is used.
     * @param string[] $defIds
     * @param Resolver[] $resolvers
     * @throws ContainerExceptionInterface
     * @return void
     */
    protected function validateConfig($defIds, $resolvers)
    {
        $validationContainer = new ValidationContainer($resolvers);
        foreach ($defIds as $id) {
            $validationContainer->get($id);
        }
    }
}
