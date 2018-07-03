<?php

namespace SilverStripe\TextExtraction\Cache;

use SilverStripe\Assets\File;

interface FileTextCache
{
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
