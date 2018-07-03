<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\TikaServerTextExtractor;

/**
 * @group tika-tests
 */
class TikaServerTextExtractorTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testServerExtraction()
    {
        $extractor = TikaServerTextExtractor::create();
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
