<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\TikaServerTextExtractor;
use SilverStripe\TextExtraction\Extractor\TikaTextExtractor;

/**
 * Tests the {@see TikaTextExtractor} class
 */
class TikaTextExtractorTest extends SapphireTest
{
    public function testExtraction()
    {
        $extractor = new TikaTextExtractor();
        if (!$extractor->isAvailable()) {
            $this->markTestSkipped('tika cli not available');
        }

        // Check file
        $file = dirname(__FILE__) . '/fixtures/test1.pdf';
        $content = $extractor->getContent($file);
        $this->assertContains('This is a test file with a link', $content);

        // Check mime validation
        $this->assertTrue($extractor->supportsMime('application/pdf'));
        $this->assertTrue($extractor->supportsMime('text/html'));
        $this->assertFalse($extractor->supportsMime('application/not-supported'));
    }

    public function testServerExtraction()
    {
        $extractor = new TikaServerTextExtractor();
        if (!$extractor->isAvailable()) {
            $this->markTestSkipped('tika server not available');
        }

        // Check file
        $file = dirname(__FILE__) . '/fixtures/test1.pdf';
        $content = $extractor->getContent($file);
        $this->assertContains('This is a test file with a link', $content);

        // Check mime validation
        $this->assertTrue($extractor->supportsMime('application/pdf'));
        $this->assertTrue($extractor->supportsMime('text/html'));
        $this->assertFalse($extractor->supportsMime('application/not-supported'));
    }
}
