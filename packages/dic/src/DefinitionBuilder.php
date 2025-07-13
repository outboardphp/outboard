<?php

declare(strict_types=1);

namespace Outboard\Di;

class DefinitionBuilder
{
    protected Definition $rule;

    public function __construct()
    {
        $this->rule = new Definition();
    }

    public function build(): Definition
    {
        return $this->rule;
    }

    /**
     * Create instances using this rule as singletons within the container, or set a specific scope.
     * Accepts true/false, 'request', or 'session'.
     */
    public function singleton(bool|string $singleton = true): static
    {
        $this->rule->singleton = $singleton;
        return $this;
    }

    /**
     * Prevent this rule from applying to child classes.
     */
    public function strict(): static
    {
        $this->rule->strict = true;
        return $this;
    }

    /**
     * Always substitute the requested class with another class, a pre-existing instance,
     * or the return value of a callable (factory).
     * Parameter typehints on a callable will be resolved by the container.
     */
    public function substitute(string|callable|object $substitute): static
    {
        $this->rule->substitute = $substitute;
        return $this;
    }

    /**
     * Supply parameters to the constructor that will be called.
     * They can be named, positional, or typed, and can be supplied as a single associative array or several parameters.
     * The array form is required for typed parameters.
     */
    public function withParams(...$params): static
    {
        // If we were passed an array, unwrap it
        if (is_array($params[0]) && count($params) === 1) {
            $params = current($params) ?: [];
        }
        $this->rule->withParams = $params;
        return $this;
    }

    /**
     * List container ids that are to be singletons within the current object graph.
     * @param string[] $ids
     */
    public function singletonsInTree(array $ids): static
    {
        $this->rule->singletonsInTree = $ids;
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
    public function call(callable $callable): static
    {
        $this->rule->call = $callable;
        return $this;
    }

    /**
     * Add tags for service tagging.
     */
    public function tags(array $tags): static
    {
        $this->rule->tags = $tags;
        return $this;
    }
}
