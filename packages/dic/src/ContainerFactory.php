<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\DefinitionProvider;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\ValueObjects\Definition;
use Psr\Container\ContainerExceptionInterface;

class ContainerFactory
{
    /**
     * @param class-string<Resolver>[] $resolvers
     */
    public function __construct(
        protected ?DefinitionProvider $definitionProvider = null,
        protected array $resolvers = [
            AutowiringResolver::class,
            Resolver::class,
        ],
    ) {}

    /**
     * @throws ContainerExceptionInterface|\ReflectionException
     */
    public function __invoke(): Container
    {
        $defs = $this->definitionProvider?->getDefinitions() ?? [];
        $resolvers = \array_map(
            fn(string $resolverClass) => $this->buildResolver($resolverClass, $defs),
            $this->resolvers,
        );
        $this->validateConfig(\array_keys($defs), $resolvers);
        return new Container($resolvers);
    }

    /**
     * @param class-string $resolverClass
     * @param array<string, Definition> $definitions
     * @throws ContainerException
     */
    protected function buildResolver(string $resolverClass, array $definitions): Resolver
    {
        $candidate = new $resolverClass($definitions);

        if ($candidate instanceof Resolver) {
            return $candidate;
        }

        if (\is_callable($candidate)) {
            $resolver = $candidate();
            if ($resolver instanceof Resolver) {
                return $resolver;
            }
        }

        throw new ContainerException("Resolver class '{$resolverClass}' must create a Resolver instance.");
    }

    /**
     * @throws ContainerExceptionInterface|\ReflectionException
     */
    public function build(): Container
    {
        return $this();
    }

    /**
     * Validates the configuration of the container by ensuring that all
     * definitions can be resolved without actually constructing any instances.
     * This is useful to catch circular dependencies or missing definitions
     * before the container is used.
     *
     * @param string[] $defIds
     * @param Resolver[] $resolvers
     * @return void
     * @throws ContainerExceptionInterface|\ReflectionException
     */
    protected function validateConfig($defIds, $resolvers)
    {
        $validationContainer = new ValidationContainer($resolvers);
        foreach ($defIds as $id) {
            $validationContainer->get($id);
        }
    }
}
