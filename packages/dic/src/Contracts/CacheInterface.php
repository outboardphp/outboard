<?php

namespace Outboard\Di\Contracts;

/**
 * Interface for caching DI container instances and factories.
 *
 * This is NOT a general-purpose cache (PSR-6/PSR-16) but specifically
 * designed for DI container needs with two distinct cache types:
 * - Shared instances (singletons)
 * - Factories (for creating non-shared instances)
 */
interface CacheInterface
{
    /**
     * Check if a shared instance exists in the cache.
     *
     * @param string $id
     * @return bool
     */
    public function hasShared($id);

    /**
     * Retrieve a shared instance from the cache.
     *
     * @param string $id
     * @throws \Outboard\Di\Exception\NotFoundException if the instance doesn't exist
     * @return mixed
     */
    public function getShared($id);

    /**
     * Store a shared instance in the cache.
     *
     * @param string $id
     * @param mixed $instance
     */
    public function setShared($id, $instance);

    /**
     * Check if a factory exists in the cache.
     *
     * @param string $id
     * @return bool
     */
    public function hasFactory($id);

    /**
     * Retrieve a factory from the cache.
     *
     * @param string $id
     * @throws \Outboard\Di\Exception\NotFoundException if the factory doesn't exist
     * @return callable
     */
    public function getFactory($id);

    /**
     * Store a factory in the cache.
     *
     * @param string $id
     * @param callable $factory
     */
    public function setFactory($id, $factory);

    /**
     * Remove a specific service from both caches.
     *
     * @param string $id
     */
    public function clear($id): void;

    /**
     * Clear all cached instances and factories.
     */
    public function clearAll(): void;
}
