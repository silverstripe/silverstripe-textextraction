<?php

namespace SilverStripe\TextExtraction\Extension;

use SilverStripe\Assets\File,
    SilverStripe\Core\Config\Config,
    SilverStripe\TextExtraction\Extension\FileTextCache;

/**
 * Caches the extracted content on the record for the file.
 * Limits the stored file content by default to avoid hitting query size limits.
 */
class FileTextCache_Database implements FileTextCache
{
    /**
     *
     * @param  File $file
     * @return FileTextCache
     */
    public function load(File $file)
    {
        return $file->FileContentCache;
    }

    /**
     * @param File $file
     * @param mixed $content
     */
    public function save(File $file, $content)
    {
        $maxLength = Config::inst()->get('FileTextCache_Database', 'max_content_length');
        $file->FileContentCache = ($maxLength) ? substr($content, 0, $maxLength) : $content;
        $file->write();
    }

    /**
     * @param File $file
     * @return void
     */
    public function invalidate(File $file)
    {
        // To prevent writing to the cache from invalidating it
        if (!$file->isChanged('FileContentCache')) {
            $file->FileContentCache = '';
        }
    }
}
