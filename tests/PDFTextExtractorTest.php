<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor\Exception;
use SilverStripe\TextExtraction\Extractor\PDFTextExtractor;

class PDFTextExtractorTest extends SapphireTest
{
    public function testExtraction()
    {
        $extractor = new PDFTextExtractor();
        if (!$extractor->isAvailable()) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('getRawOutput called on unavailable extractor');
        }

        $content = $extractor->getContent(dirname(__FILE__) . '/fixtures/test1.pdf');
        $this->assertContains('This is a test file with a link', $content);
    }
}
