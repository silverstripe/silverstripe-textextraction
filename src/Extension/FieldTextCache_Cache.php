<?php

namespace SilverStripe\TextExtraction\Extension;

use SilverStripe\Assets\File,
    SilverStripe\Core\Config\Config,
    SilverStripe\TextExtraction\Extension\FileTextCache,
    SilverStripe\Core\Flushable,
    Psr\SimpleCache\CacheInterface,
    SilverStripe\Core\Injector\Injector;

/**
 * Uses SS_Cache with a lifetime to cache extracted content
 */
class FileTextCache_Cache implements FileTextCache, Flushable
{
    /**
     * Lifetime of cache in seconds
     * Null is indefinite
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
        return md5($file->getFilename());
    }

    /**
     *
     * @param File $file
     * @return type
     */
    public function load(File $file)
    {
        $key = $this->getKey($file);
        $cache = self::get_cache();

        return $cache->get($key);
    }

    /**
     * @param  File $file
     * @param  string $content
     * @return string
     */
    public function save(File $file, $content)
    {
        $lifetime = Config::inst()->get(__CLASS__, 'lifetime');
        $lifetime = $lifetime ?: 3600;
        $key = $this->getKey($file);
        $cache = self::get_cache();

        return $cache->set($key, $content, $lifetime);
    }

    /**
     * @return void
     */
    public static function flush()
    {
        $cache = self::get_cache();
        $cache->clear();
    }

    /**
     * Alias for $this->flush()
     *
     * @return void
     */
    public static function clear()
    {
        $cache = self::get_cache();
        $cache->clear();
    }

    /**
     *
     * @param File $file
     * @return type
     */
    public function invalidate(File $file)
    {
        $key = $this->getKey($file);
        $cache = self::get_cache();

        return $cache->delete($key);
    }
}
