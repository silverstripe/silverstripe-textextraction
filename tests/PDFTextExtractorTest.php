<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor\Exception;
use SilverStripe\TextExtraction\Extractor\PDFTextExtractor;

class PDFTextExtractorTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testExtraction()
    {
        $extractor = new PDFTextExtractor();
        if (!$extractor->isAvailable()) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('getRawOutput called on unavailable extractor');
        }

        $file = new File();
        $file->setFromLocalFile(dirname(__FILE__) . '/fixtures/test1.pdf');
        $file->write();

        $content = $extractor->getContent($file);
        $this->assertContains('This is a test file with a link', $content);
    }
}
