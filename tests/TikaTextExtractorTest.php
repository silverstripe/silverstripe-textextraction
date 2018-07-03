<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\TikaServerTextExtractor;
use SilverStripe\TextExtraction\Extractor\TikaTextExtractor;

/**
 * Tests the {@see TikaTextExtractor} class
 */
class TikaTextExtractorTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testExtraction()
    {
        $extractor = new TikaTextExtractor();
        if (!$extractor->isAvailable()) {
            $this->markTestSkipped('tika cli not available');
        }

        // Check file
        $file = new File();
        $file->setFromLocalFile(dirname(__FILE__) . '/fixtures/test1.pdf');
        $file->write();

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
        $file = new File();
        $file->setFromLocalFile(dirname(__FILE__) . '/fixtures/test1.pdf');
        $file->write();

        $content = $extractor->getContent($file);
        $this->assertContains('This is a test file with a link', $content);

        // Check mime validation
        $this->assertTrue($extractor->supportsMime('application/pdf'));
        $this->assertTrue($extractor->supportsMime('text/html'));
        $this->assertFalse($extractor->supportsMime('application/not-supported'));
    }
}
