<?php

namespace SilverStripe\TextExtraction\Extractor;

use SilverStripe\Assets\File;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor\Exception;

/**
 * A decorator for File or a subclass that provides a method for extracting full-text from the file's external contents.
 * @author mstephens
 */
abstract class FileTextExtractor
{
    use Configurable;
    use Injectable;

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
    protected static function get_extractor_classes()
    {
        // Check cache
        if (FileTextExtractor::$sorted_extractor_classes) {
            return FileTextExtractor::$sorted_extractor_classes;
        }

        // Generate the sorted list of extractors on demand.
        $classes = ClassInfo::subclassesFor(__CLASS__);
        array_shift($classes);
        $classPriorities = [];

        foreach ($classes as $class) {
            $classPriorities[$class] = Config::inst()->get($class, 'priority');
        }
        arsort($classPriorities);

        // Save classes
        $sortedClasses = array_keys($classPriorities ?? []);
        return FileTextExtractor::$sorted_extractor_classes = $sortedClasses;
    }

    /**
     * Get the text file extractor for the given class
     *
     * @param string $class
     * @return FileTextExtractor
     */
    protected static function get_extractor($class)
    {
        return Injector::inst()->get($class);
    }

    /**
     * Given a File object, decide which extractor instance to use to handle it
     *
     * @param File|string $file
     * @return FileTextExtractor|null
     */
    public static function for_file($file)
    {
        if (!$file || (is_string($file) && !file_exists($file ?? ''))) {
            return null;
        }

        // Ensure we have a File instance to work with
        if (is_string($file)) {
            $fileObject = File::create();
            $fileObject->setFromLocalFile($file);
            $file = $fileObject;
        }

        $extension = $file->getExtension();
        $mime = $file->getMimeType();

        foreach (FileTextExtractor::get_extractor_classes() as $className) {
            $extractor = FileTextExtractor::get_extractor($className);

            // Skip unavailable extractors
            if (!$extractor->isAvailable()) {
                continue;
            }

            // Check extension
            if ($extension && $extractor->supportsExtension($extension)) {
                return $extractor;
            }

            // Check mime
            if ($mime && $extractor->supportsMime($mime)) {
                return $extractor;
            }
        }
    }

    /**
     * Some text extractors (like pdftotext) may require a physical file to read from, so write the current
     * file contents to a temp file and return its path
     *
     * @param File $file
     * @return string
     * @throws Exception
     */
    protected static function getPathFromFile(File $file)
    {
        $path = tempnam(TEMP_PATH, 'pdftextextractor_');
        if (false === $path) {
            throw new Exception(static::class . '->getPathFromFile() could not allocate temporary file name');
        }

        // Append extension to temp file if one is set
        if ($file->getExtension()) {
            $path .= '.' . $file->getExtension();
        }

        // Remove any existing temp files with this name
        if (file_exists($path ?? '')) {
            unlink($path ?? '');
        }

        $bytesWritten = file_put_contents($path ?? '', $file->getStream());
        if (false === $bytesWritten) {
            throw new Exception(static::class . '->getPathFromFile() failed to write temporary file');
        }

        return $path;
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
     * Determine if this extractor supports the given mime type.
     * Will only be called if supportsExtension returns false.
     *
     * @param string $mime
     * @return boolean
     */
    abstract public function supportsMime($mime);

    /**
     * Given a File instance, extract the contents as text.
     *
     * @param File|string $file Either the File instance, or a file path for a file to load
     * @return string
     */
    abstract public function getContent($file);
}
