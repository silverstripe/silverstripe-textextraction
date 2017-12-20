<?php

namespace SilverStripe\TextExtraction\Extension;

use SilverStripe\ORM\DataExtension,
    SilverStripe\TextExtraction\Extension\FileTextCache,
    SilverStripe\Control\Director;

/**
 * Decorate File or a File derivative to enable text extraction from the file content. Uses a set of subclasses of
 * FileTextExtractor to do the extraction based on the content type of the file.
 *
 * Adds an additional property which is the cached contents, which is populated on demand.
 *
 * @author mstephens
 *
 */
class FileTextExtractable extends DataExtension
{
    /**
     *
     * @var array
     * @config
     */
    private static $db = array(
        'FileContentCache' => 'Text'
    );

    /**
     *
     * @var array
     * @config
     */
    private static $casting = array(
        'FileContent' => 'Text'
    );

    /**
     *
     * @var array
     * @config
     */
    private static $dependencies = array(
        'TextCache' => '%$SilverStripe\TextExtraction\Extension\FileTextCache_Cache'
    );

    /**
     * @var FileTextCache
     */
    protected $fileTextCache = null;

    /**
     *
     * @param  FileTextCache $cache
     * @return void
     */
    public function setTextCache(FileTextCache $cache)
    {
        $this->fileTextCache = $cache;
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
     * Tries to parse the file contents if a FileTextExtractor class exists to handle the file type, and returns the text.
     * The value is also cached into the File record itself.
     *
     * @param boolean $disableCache If false, the file content is only parsed on demand.
     *                              If true, the content parsing is forced, bypassing
     *                              the cached version
     * @return mixed string | null
     */
    public function extractFileAsText($disableCache = false)
    {
        if (!$disableCache) {
            $text = $this->getTextCache()->load($this->owner);
            if ($text) {
                return $text;
            }
        }

        // Determine which extractor can process this file.
        $path = Director::baseFolder() . '/' . $this->owner->getFilename();
        $extractor = FileTextExtractor::for_file($path);
        if (!$extractor) {
            return null;
        }

        $text = $extractor->getContent($path);
        if (!$text) {
            return null;
        }

        if (!$disableCache) {
            $this->getTextCache()->save($this->owner, $text);
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
