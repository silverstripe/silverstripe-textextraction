<?php

namespace SilverStripe\TextExtraction\Extension;

use SilverStripe\Assets\File;
use SilverStripe\ORM\DataExtension;
use SilverStripe\TextExtraction\Cache\FileTextCache;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;

/**
 * Decorate File or a File derivative to enable text extraction from the file content. Uses a set of subclasses of
 * FileTextExtractor to do the extraction based on the content type of the file.
 *
 * Adds an additional property which is the cached contents, which is populated on demand.
 *
 * @author mstephens
 *
 * @extends DataExtension<File>
 */
class FileTextExtractable extends DataExtension
{
    /**
     * @var array
     * @config
     */
    private static $db = [
        'FileContentCache' => 'Text'
    ];

    /**
     * @var array
     * @config
     */
    private static $casting = [
        'FileContent' => 'Text'
    ];

    /**
     * @var array
     * @config
     */
    private static $dependencies = [
        'TextCache' => '%$' . FileTextCache::class,
    ];

    /**
     * @var FileTextCache
     */
    protected $fileTextCache = null;

    /**
     * @param  FileTextCache $cache
     * @return $this
     */
    public function setTextCache(FileTextCache $cache)
    {
        $this->fileTextCache = $cache;
        return $this;
    }

    /**
     * @return FileTextCache
     */
    public function getTextCache()
    {
        return $this->fileTextCache;
    }

    /**
     * Helper function for template
     *
     * @return string
     */
    public function getFileContent()
    {
        return $this->extractFileAsText();
    }

    /**
     * Tries to parse the file contents if a FileTextExtractor class exists to handle the file type, and
     * returns the text. The value is also cached into the File record itself.
     *
     * @param boolean $disableCache If false, the file content is only parsed on demand.
     *                              If true, the content parsing is forced, bypassing
     *                              the cached version
     * @return string|null
     */
    public function extractFileAsText($disableCache = false)
    {
        $file = $this->owner;
        if (!$disableCache) {
            $text = $this->getTextCache()->load($file);
            if ($text) {
                return $text;
            }
        }

        // Determine which extractor can process this file.
        $extractor = FileTextExtractor::for_file($file);
        if (!$extractor) {
            return null;
        }

        $text = $extractor->getContent($file);
        if (!$text) {
            return null;
        }

        if (!$disableCache) {
            $this->getTextCache()->save($file, $text);
        }

        return $text;
    }

    /**
     * @return void
     */
    public function onBeforeWrite()
    {
        // Clear cache before changing file
        $this->getTextCache()->invalidate($this->owner);
    }
}
