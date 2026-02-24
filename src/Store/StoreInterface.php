<?php

namespace Doppar\AI\Store;

interface StoreInterface
{
    /**
     * Store an agent
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    public function store(string $key, mixed $data): bool;

    /**
     * Load an agent from store
     *
     * @param string $key
     * @return mixed
     */
    public function load(string $key): mixed;

    /**
     * Check if a key exists in store
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Delete a key from store
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Clear all store
     *
     * @return bool
     */
    public function clear(): bool;
}
