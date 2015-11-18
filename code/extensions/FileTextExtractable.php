<?php

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
    private static $db = array(
        'FileContentCache' => 'Text'
    );

    private static $casting = array(
        'FileContent' => 'Text'
    );

    private static $dependencies = array(
        'TextCache' => '%$FileTextCache'
    );

    /**
     * @var FileTextCache
     */
    protected $fileTextCache = null;

    /**
     *
     * @param FileTextCache $cache
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
     * If true, the content parsing is forced, bypassing the cached version
     * @return string
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
        $extractor = FileTextExtractor::for_file($this->owner->FullPath);
        if (!$extractor) {
            return null;
        }

        $text = $extractor->getContent($this->owner->FullPath);
        if (!$text) {
            return null;
        }

        $this->getTextCache()->save($this->owner, $text);

        return $text;
    }

    public function onBeforeWrite()
    {
        // Clear cache before changing file
        $this->getTextCache()->invalidate($this->owner);
    }
}
