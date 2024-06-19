<?php

namespace SilverStripe\TextExtraction\Cache\FileTextCache;

use Psr\SimpleCache\CacheInterface;
use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Flushable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\TextExtraction\Cache\FileTextCache;

/**
 * Uses SS_Cache with a lifetime to cache extracted content
 */
class Cache implements FileTextCache, Flushable
{
    use Configurable;

    /**
     * Lifetime of cache in seconds
     * Null defaults to 3600 (1 hour)
     *
     * @var int|null
     * @config
     */
    private static $lifetime = null;

    /**
     * @return CacheInterface
     */
    protected static function get_cache()
    {
        $for = sprintf('%s.%s', CacheInterface::class, 'FileTextCache_Cache');

        return Injector::inst()->get($for);
    }

    /**
     *
     * @param  File $file
     * @return string
     */
    protected function getKey(File $file)
    {
        return md5($file->getFilename() ?? '');
    }

    /**
     *
     * @param File $file
     * @return mixed
     */
    public function load(File $file)
    {
        $key = $this->getKey($file);
        $cache = Cache::get_cache();

        return $cache->get($key);
    }

    /**
     * @param  File $file
     * @param  string $content
     * @return string
     */
    public function save(File $file, $content)
    {
        $lifetime = $this->config()->get('lifetime') ?: 3600;
        $key = $this->getKey($file);
        $cache = Cache::get_cache();

        return $cache->set($key, $content, $lifetime);
    }

    /**
     * @return void
     */
    public static function flush()
    {
        $cache = Cache::get_cache();
        $cache->clear();
    }

    /**
     * Alias for $this->flush()
     *
     * @return void
     */
    public static function clear()
    {
        $cache = Cache::get_cache();
        $cache->clear();
    }

    /**
     *
     * @param File $file
     * @return bool
     */
    public function invalidate(File $file)
    {
        $key = $this->getKey($file);
        $cache = Cache::get_cache();

        return $cache->delete($key);
    }
}
