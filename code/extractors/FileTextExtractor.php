<?php

/**
 * A decorator for File or a subclass that provides a method for extracting full-text from the file's external contents.
 * @author mstephens
 *
 */
abstract class FileTextExtractor extends Object {

	/**
	 * Set priority from 0-100.
	 * The highest priority extractor for a given content type will be selected.
	 *
	 * @config
	 * @var integer
	 */
	private static $priority = 50;

	/**
	 * Cache of extractor class names, sorted by priority
	 *
	 * @var array
	 */
	protected static $sorted_extractor_classes = null;

	/**
	 * Gets the list of prioritised extractor classes
	 *
	 * @return array
	 */
	protected static function get_extractor_classes() {
		// Check cache
		if (self::$sorted_extractor_classes) return self::$sorted_extractor_classes;
		
		// Generate the sorted list of extractors on demand.
		$classes = ClassInfo::subclassesFor("FileTextExtractor");
		array_shift($classes);
		$classPriorities = array();
		foreach($classes as $class) {
			$classPriorities[$class] = Config::inst()->get($class, 'priority');
		}
		arsort($classPriorities);

		// Save classes
		$sortedClasses = array_keys($classPriorities);
		return self::$sorted_extractor_classes = $sortedClasses;
	}

	/**
	 * Get the text file extractor for the given class
	 *
	 * @param string $class
	 * @return FileTextExtractor
	 */
	protected static function get_extractor($class) {
		return Injector::inst()->get($class);
	}

	/**
	 * Attempt to detect mime type for given file
	 *
	 * @param string $path
	 * @return string Mime type if found
	 */
	protected static function get_mime($path) {
		$file = new Symfony\Component\HttpFoundation\File\File($path);

		return $file->getMimeType();
	}

	/**
	 * @param string $path
	 * @return FileTextExtractor|null
	 */
	static function for_file($path) {
		if(!file_exists($path) || is_dir($path)) {
			return;
		}

		$extension = pathinfo($path, PATHINFO_EXTENSION);
		$mime = self::get_mime($path);
		foreach(self::get_extractor_classes() as $className) {
			$extractor = self::get_extractor($className);

			// Skip unavailable extractors
			if(!$extractor->isAvailable()) continue;

			// Check extension
			if($extension && $extractor->supportsExtension($extension)) {
				return $extractor;
			}

			// Check mime
			if($mime && $extractor->supportsMime($mime)) {
				return $extractor;
			}
		}
	}

	/**
	 * Checks if the extractor is supported on the current environment,
	 * for example if the correct binaries or libraries are available.
	 * 
	 * @return boolean
	 */
	abstract public function isAvailable();

	/**
	 * Determine if this extractor supports the given extension.
	 * If support is determined by mime/type only, then this should return false.
	 *
	 * @param string $extension
	 * @return boolean
	 */
	abstract public function supportsExtension($extension);

	/**
	 * Determine if this extractor suports the given mime type.
	 * Will only be called if supportsExtension returns false.
	 * 
	 * @param string $mime
	 * @return boolean
	 */
	abstract public function supportsMime($mime);

	/**
	 * Given a file path, extract the contents as text.
	 * 
	 * @param string $path
	 * @return string
	 */
	abstract public function getContent($path);
}

class FileTextExtractor_Exception extends Exception {}