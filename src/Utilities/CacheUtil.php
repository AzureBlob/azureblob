<?php

namespace AzureBlob\Utilities;

final class CacheUtil implements CacheUtilityInterface
{
    public const DEFAULT_CACHE_DIR = '/tmp';
    public const DEFAULT_CACHE_TTL = 300;

    private string $cacheDir;
    private int $lifespanInSeconds;

    /**
     * @param string $cacheDir
     * @param int $lifespanInSeconds
     */
    public function __construct(
        string $cacheDir = self::DEFAULT_CACHE_DIR,
        int $lifespanInSeconds = self::DEFAULT_CACHE_TTL
    ) {
        $this->cacheDir = $cacheDir;
        $this->lifespanInSeconds = $lifespanInSeconds;
    }

    public function isCached(string $cacheKey): bool
    {
        $cacheItem = $this->getCacheItem($cacheKey);
        if(!file_exists($cacheItem)) {
            return false;
        }
        $expirationTime = time() - $this->lifespanInSeconds;
        if ($expirationTime > filemtime($cacheItem)) {
            unlink($cacheItem);
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function load(string $cacheKey)
    {
        if (!$this->isCached($cacheKey)) {
            return false;
        }
        return unserialize(file_get_contents($this->getCacheItem($cacheKey)));
    }

    /**
     * @inheritDoc
     */
    public function save(string $cacheKey, $data): bool
    {
        $cacheFile = $this->getCacheItem($cacheKey);
        $result = file_put_contents($cacheFile, serialize($data));
        return is_int($result);
    }

    /**
     * @inheritDoc
     */
    public function purge(string $cacheKey): bool
    {
        $cacheItem = $this->getCacheItem($cacheKey);
        if (!file_exists($cacheItem)) {
            return true;
        }
        unlink($cacheItem);
        return true;
    }

    private function getCacheItem($cacheKey): string
    {
        return sprintf('%s/%s', $this->cacheDir, $cacheKey);
    }
}