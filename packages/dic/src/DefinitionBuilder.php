<?php

declare(strict_types=1);

namespace Outboard\Di;

use Outboard\Di\Enums\Scope;
use Outboard\Di\ValueObjects\Definition;

class DefinitionBuilder
{
    protected bool|Scope $shared = false;

    protected bool $strict = false;

    /** @var string|callable|object|null */
    protected mixed $substitute = null;

    /** @var mixed[] */
    protected array $withParams = [];

    /** @var string[] */
    protected array $sharedInTree = [];

    /** @var callable|null */
    protected mixed $call = null;

    /** @var string[] */
    protected array $tags = [];

    public function build(): Definition
    {
        return new Definition(
            shared: $this->shared,
            strict: $this->strict,
            substitute: $this->substitute,
            withParams: $this->withParams,
            sharedInTree: $this->sharedInTree,
            call: $this->call,
            tags: $this->tags,
        );
    }

    /**
     * Share instances using this rule within the container or set a specific scope.
     * Accepts true/false, 'request', or 'session'.
     */
    public function shared(bool|Scope $shared = true): static
    {
        $this->shared = $shared;
        return $this;
    }

    /**
     * Prevent this rule from applying to child classes.
     */
    public function strict(bool $strict = true): static
    {
        $this->strict = $strict;
        return $this;
    }

    /**
     * Always substitute the requested class with another class, a pre-existing instance,
     * or the return value of a callable (factory).
     * Parameter typehints on a callable will be resolved by the container.
     */
    public function substitute(string|callable|object|null $substitute): static
    {
        $this->substitute = $substitute;
        return $this;
    }

    /**
     * Supply parameters to the constructor that will be called.
     * They can be named, positional, or typed, and can be supplied as a single associative array or several parameters.
     * The array form is required for typed parameters.
     *
     * @param array<int|string, mixed>|mixed ...$params
     */
    public function withParams(...$params): static
    {
        // If we were passed an array, unwrap it
        if (\is_array($params[0]) && \count($params) === 1) {
            $params = \current($params) ?: [];
        }
        $this->withParams = $params;
        return $this;
    }

    /**
     * List container ids that are to be shared within the current object graph.
     * @param string[] $ids
     */
    public function sharedInTree(array $ids): static
    {
        $this->sharedInTree = $ids;
        return $this;
    }

    /**
     * Call this after the instance has been constructed.
     * This callable will receive a CallWrapper object which composes the real instance and forwards calls to it.
     * It allows you to provide scalar parameters here while resolving typed parameters from the container.
     * It is also built to allow both fluent method chaining and sequential regular method calls.
     * If the callable returns an object, it is considered to be a decorator and the instance will be replaced
     * with the returned object as long as it is type-compatible with the original class.
     */
    public function call(?callable $callable): static
    {
        $this->call = $callable;
        return $this;
    }

    /**
     * Add tags for service tagging.
     *
     * @param string[] $tags
     */
    public function tags(array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }
}
