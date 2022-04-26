<?php

namespace SilverStripe\TextExtraction\Extractor;

use SilverStripe\Assets\File;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor\Exception;

/**
 * Text extractor that calls pdftotext to do the conversion.
 * @author mstephens
 */
class PDFTextExtractor extends FileTextExtractor
{
    /**
     * Set to bin path this extractor can execute
     *
     * @var string
     */
    private static $binary_location = null;

    /**
     * Used if binary_location isn't set.
     * List of locations to search for a given binary in
     *
     * @config
     * @var array
     */
    private static $search_binary_locations = [
        '/usr/bin',
        '/usr/local/bin',
    ];

    public function isAvailable()
    {
        $bin = $this->bin('pdftotext');
        return $bin && file_exists($bin ?? '') && is_executable($bin ?? '');
    }

    public function supportsExtension($extension)
    {
        return strtolower($extension ?? '') === 'pdf';
    }

    public function supportsMime($mime)
    {
        return in_array(
            strtolower($mime ?? ''),
            [
                'application/pdf',
                'application/x-pdf',
                'application/x-bzpdf',
                'application/x-gzpdf'
            ]
        );
    }

    /**
     * Accessor to get the location of the binary
     *
     * @param string $program Name of binary
     * @return string
     */
    protected function bin($program = '')
    {
        // Get list of allowed search paths
        if ($location = $this->config()->get('binary_location')) {
            $locations = [$location];
        } else {
            $locations = $this->config()->get('search_binary_locations');
        }

        // Find program in each path
        foreach ($locations as $location) {
            $path = "{$location}/{$program}";
            if (file_exists($path ?? '')) {
                return $path;
            }
            if (file_exists($path . '.exe')) {
                return $path . '.exe';
            }
        }

        // Not found
        return null;
    }

    public function getContent($file)
    {
        if (!$file || (is_string($file) && !file_exists($file ?? ''))) {
            // no file
            return '';
        }
        $content = $this->getRawOutput($file);
        return $this->cleanupLigatures($content);
    }

    /**
     * Invoke pdftotext with the given File object
     *
     * @param  File|string $file
     * @return string Output
     * @throws Exception
     */
    protected function getRawOutput($file)
    {
        if (!$this->isAvailable()) {
            throw new Exception("getRawOutput called on unavailable extractor");
        }

        $path = $file instanceof File ? $this->getPathFromFile($file) : $file;
        exec(sprintf('%s %s - 2>&1', $this->bin('pdftotext'), escapeshellarg($path ?? '')), $content, $err);

        if ($err) {
            throw new Exception(sprintf(
                'PDFTextExtractor->getContent() failed for %s: %s',
                $path,
                implode(PHP_EOL, $content)
            ));
        }

        return implode(PHP_EOL, $content);
    }

    /**
     * Removes utf-8 ligatures.
     *
     * @link http://en.wikipedia.org/wiki/Typographic_ligature#Computer_typesetting
     *
     * @param string $input
     * @return string
     */
    protected function cleanupLigatures($input)
    {
        $mapping = [
            'ﬀ' => 'ff',
            'ﬁ' => 'fi',
            'ﬂ' => 'fl',
            'ﬃ' => 'ffi',
            'ﬄ' => 'ffl',
            'ﬅ' => 'ft',
            'ﬆ' => 'st'
        ];

        return str_replace(array_keys($mapping ?? []), array_values($mapping ?? []), $input ?? '');
    }
}
