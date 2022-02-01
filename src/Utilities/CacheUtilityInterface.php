<?php
declare(strict_types=1);

namespace AzureBlob\Utilities;

interface CacheUtilityInterface
{
    /**
     * Loads contents from a cache or returns FALSE when
     * cache is not found or is expired
     *
     * @param string $cacheKey
     * @return mixed
     */
    public function load(string $cacheKey);

    /**
     * Saves data to the cache and returns TRUE when successful
     * or FALSE when saving data failed.
     *
     * @param string $cacheKey
     * @param mixed $data
     * @return bool
     */
    public function save(string $cacheKey, $data): bool;

    /**
     * Invalidates a specific cache
     *
     * @param string $cacheKey
     * @return bool
     */
    public function purge(string $cacheKey): bool;
}