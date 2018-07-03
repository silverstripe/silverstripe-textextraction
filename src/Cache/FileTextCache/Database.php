<?php

namespace SilverStripe\TextExtraction\Cache\FileTextCache;

use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\TextExtraction\Cache\FileTextCache;

/**
 * Caches the extracted content on the record for the file.
 * Limits the stored file content by default to avoid hitting query size limits.
 */
class Database implements FileTextCache
{
    use Configurable;

    /**
     * @config
     * @var int
     */
    private static $max_content_length = null;

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
        $maxLength = $this->config()->get('max_content_length');
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
