<?php

namespace Outboard\Di\Substitution;

use Outboard\Di\Contracts\SubstitutionHandlerInterface;
use Outboard\Di\Contracts\SubstitutionResolverInterface;
use Outboard\Di\Exception\ContainerException;
use Outboard\Di\ValueObjects\Definition;
use Outboard\Di\ValueObjects\SubstitutionResolution;
use Psr\Container\ContainerInterface;

class SubstitutionResolverChain implements SubstitutionResolverInterface
{
    /**
     * @param list<SubstitutionHandlerInterface> $handlers
     */
    public function __construct(
        protected array $handlers = [],
    ) {
        if ($this->handlers === []) {
            $this->handlers = [
                new CallableSubstitutionHandler(),
                new ObjectSubstitutionHandler(),
                new StringSubstitutionHandler(),
                new NullSubstitutionHandler(),
            ];
        }
    }

    /**
     * @throws ContainerException
     */
    public function resolve(
        string $id,
        Definition $definition,
        ContainerInterface $container,
    ): SubstitutionResolution {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($definition)) {
                return $handler->resolve($id, $definition, $container);
            }
        }

        throw new ContainerException('No substitution handler matched for definition.');
    }
}
