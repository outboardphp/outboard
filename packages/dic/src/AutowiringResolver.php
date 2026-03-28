<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\ImplicitResolvablePolicyInterface;
use Outboard\Di\Contracts\ParameterApplicatorInterface;
use Outboard\Di\Contracts\SubstitutionResolverInterface;
use Outboard\Di\Matching\DefinitionMatcher;
use Outboard\Di\Parameter\AutowiringParameterApplicator;
use Outboard\Di\Support\ClassExistsImplicitResolvablePolicy;
use Outboard\Di\Support\DefinitionIdNormalizer;
use Outboard\Di\Support\PostCallDecorator;
use Outboard\Di\Substitution\SubstitutionResolverChain;
use Outboard\Di\ValueObjects\Definition;

class AutowiringResolver
{
    /**
     * @param array<string, Definition> $definitions
     */
    public function __construct(
        protected array $definitions = [],
        protected DefinitionIdNormalizer $definitionIdNormalizer = new DefinitionIdNormalizer(),
        protected DefinitionMatcher $definitionMatcher = new DefinitionMatcher(),
        protected SubstitutionResolverInterface $substitutionResolver = new SubstitutionResolverChain(),
        protected PostCallDecorator $postCallDecorator = new PostCallDecorator(),
        protected ParameterApplicatorInterface $parameterApplicator = new AutowiringParameterApplicator(),
        protected ImplicitResolvablePolicyInterface $implicitResolvablePolicy = new ClassExistsImplicitResolvablePolicy(),
    ) {
    }

    /**
     * @param array<string, Definition> $definitions
     */
    public static function create(array $definitions = []): Resolver
    {
        return new self($definitions)();
    }

    public function __invoke(): Resolver
    {
        return new Resolver(
            definitions: $this->definitions,
            definitionIdNormalizer: $this->definitionIdNormalizer,
            definitionMatcher: $this->definitionMatcher,
            substitutionResolver: $this->substitutionResolver,
            postCallDecorator: $this->postCallDecorator,
            parameterApplicator: $this->parameterApplicator,
            implicitResolvablePolicy: $this->implicitResolvablePolicy,
        );
    }

    public function build(): Resolver
    {
        return $this();
    }
}
