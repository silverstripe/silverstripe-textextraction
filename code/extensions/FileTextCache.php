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
}

/**
 * Caches the extracted content on the record for the file
 */
class FileTextCache_Database implements FileTextCache {
	
	public function load(File $file) {
		return $file->FileContentCache;
	}

	public function save(File $file, $content) {
		$file->FileContentCache = $content;
		$file->write();
	}

}

/**
 * Uses SS_Cache with a lifetime to cache extracted content
 */
class FileTextCache_SSCache implements FileTextCache, Flushable {

	/**
	 * Default cache to 1 hour
	 *
	 * @var int
	 * @config
	 */
	private static $lifetime = 3600;

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

}
