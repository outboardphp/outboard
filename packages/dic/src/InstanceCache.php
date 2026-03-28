<?php

namespace Outboard\Di;

use Outboard\Di\Contracts\CacheInterface;
use Outboard\Di\Exception\NotFoundException;

/**
 * Default in-memory implementation of CacheInterface for DI container.
 *
 * Separates caching concerns from the Container, making it easier to:
 * - Test caching behavior in isolation
 * - Swap caching strategies (e.g., LRU, TTL, memory-limited)
 * - Understand cache behavior without reading Container logic
 */
class InstanceCache implements CacheInterface
{
    /**
     * @var array<string, mixed>
     * Cached shared instances (singletons)
     */
    private array $instances = [];

    /**
     * @var array<string, callable>
     * Cached factories for non-shared services
     */
    private array $factories = [];

    /**
     * Check if a shared instance exists in the cache.
     *
     * @param string $id
     * @return bool
     */
    public function hasShared($id)
    {
        return isset($this->instances[$id]);
    }

    /**
     * Retrieve a shared instance from the cache.
     *
     * @param string $id
     * @return mixed
     * @throws NotFoundException if the instance doesn't exist
     */
    public function getShared($id)
    {
        if (!$this->hasShared($id)) {
            throw new NotFoundException("Shared instance '$id' not found in cache");
        }
        return $this->instances[$id];
    }

    /**
     * Store a shared instance in the cache.
     *
     * @param string $id
     * @param mixed $instance
     * @return void
     */
    public function setShared($id, $instance)
    {
        $this->instances[$id] = $instance;
    }

    /**
     * Check if a factory exists in the cache.
     *
     * @param string $id
     * @return bool
     */
    public function hasFactory($id)
    {
        return isset($this->factories[$id]);
    }

    /**
     * Retrieve a factory from the cache.
     *
     * @param string $id
     * @return callable
     * @throws NotFoundException if the factory doesn't exist
     */
    public function getFactory($id)
    {
        if (!$this->hasFactory($id)) {
            throw new NotFoundException("Factory for '$id' not found in cache");
        }
        return $this->factories[$id];
    }

    /**
     * Store a factory in the cache.
     *
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function setFactory($id, $factory)
    {
        $this->factories[$id] = $factory;
    }

    /**
     * Remove a specific service from both caches.
     *
     * Useful for cache invalidation or hot-reloading in development.
     *
     * @param string $id
     * @return void
     */
    public function clear($id): void
    {
        unset($this->instances[$id], $this->factories[$id]);
    }

    /**
     * Clear all cached instances and factories.
     *
     * Useful for testing or resetting the container state.
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->instances = [];
        $this->factories = [];
    }

    /**
     * Get all cached shared instance IDs.
     *
     * @return string[]
     */
    public function getSharedIds(): array
    {
        return array_keys($this->instances);
    }

    /**
     * Get all cached factory IDs.
     *
     * @return string[]
     */
    public function getFactoryIds(): array
    {
        return array_keys($this->factories);
    }

    /**
     * Get the total number of cached items (instances + factories).
     */
    public function count(): int
    {
        return count($this->instances) + count($this->factories);
    }
}

