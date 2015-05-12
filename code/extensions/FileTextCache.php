<?php

interface FileTextCache {

	/**
	 * Save extracted content for a given File entity
	 *
	 * @param File $file
	 * @param string $content
	 */
	public function save(File $file, $content);

	/**
	 * Return any cached extracted content for a given file entity
	 *
	 * @param File $file
	 */
	public function load(File $file);

	/**
	 * Invalidate the cache for a given file.
	 * Invoked in onBeforeWrite on the file
	 *
	 * @param File $file
	 */
	public function invalidate(File $file);
}

/**
 * Caches the extracted content on the record for the file.
 * Limits the stored file content by default to avoid hitting query size limits.
 */
class FileTextCache_Database implements FileTextCache {
	
	public function load(File $file) {
		return $file->FileContentCache;
	}

	public function save(File $file, $content) {
		$maxLength = Config::inst()->get('FileTextCache_Database', 'max_content_length');
		$file->FileContentCache = ($maxLength) ? substr($content, 0, $maxLength) : $content;
		$file->write();
	}

	public function invalidate(File $file) {
		// To prevent writing to the cache from invalidating it
		if(!$file->isChanged('FileContentCache')) {
			$file->FileContentCache = '';
		}
	}

}

/**
 * Uses SS_Cache with a lifetime to cache extracted content
 */
class FileTextCache_SSCache implements FileTextCache, Flushable {

	/**
	 * Lifetime of cache in seconds
	 * Null is indefinite
	 *
	 * @var int|null
	 * @config
	 */
	private static $lifetime = null;

	/**
	 * @return SS_Cache
	 */
	protected static function get_cache() {
		$lifetime = Config::inst()->get(__CLASS__, 'lifetime');
		$cache = SS_Cache::factory(__CLASS__);
		$cache->setLifetime($lifetime);
		return $cache;
	}

	protected function getKey(File $file) {
		return md5($file->getFullPath());
	}

	public function load(File $file) {
		$key = $this->getKey($file);
		$cache = self::get_cache();
		return $cache->load($key);
	}

	public function save(File $file, $content) {
		$key = $this->getKey($file);
		$cache = self::get_cache();
		return $cache->save($content, $key);
	}

	public static function flush() {
		$cache = self::get_cache();
		$cache->clean();
	}

	public function invalidate(File $file) {
		$key = $this->getKey($file);
		$cache = self::get_cache();
		return $cache->remove($key);
	}

}
