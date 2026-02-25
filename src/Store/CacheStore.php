<?php

namespace Doppar\AI\Store;

class CacheStore implements StoreInterface
{
    /**
     * Cache directory path
     *
     * @var string
     */
    protected string $cachePath;

    /**
     * Constructor
     *
     * @param string $cachePath
     */
    public function __construct(?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?? './doppar_ai_store';
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get the file path for a store key
     *
     * @param string $key
     * @return string
     */
    protected function getFilePath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.store';
    }

    /**
     * Store an agent
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    public function store(string $key, mixed $data): bool
    {
        $filePath = $this->getFilePath($key);
        $serialized = serialize($data);
        
        return file_put_contents($filePath, $serialized) !== false;
    }

    /**
     * Load an agent from store
     *
     * @param string $key
     * @return mixed
     */
    public function load(string $key): mixed
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $serialized = file_get_contents($filePath);
        
        return unserialize($serialized);
    }

    /**
     * Check if a key exists in store
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return file_exists($this->getFilePath($key));
    }

    /**
     * Delete a key from store
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        return unlink($filePath);
    }

    /**
     * Clear all store
     *
     * @return bool
     */
    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*.store');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
}
