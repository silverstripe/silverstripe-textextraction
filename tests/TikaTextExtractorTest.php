<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\TikaTextExtractor;

/**
 * Tests the {@see TikaTextExtractor} class
 *
 * @group tika-tests
 */
class TikaTextExtractorTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testExtraction()
    {
        $extractor = TikaTextExtractor::create();
        if (!$extractor->isAvailable()) {
            $this->markTestSkipped('tika cli not available');
        }

        // Check file
        $file = new File();
        $file->setFromLocalFile(dirname(__FILE__) . '/fixtures/test1.pdf');
        $file->write();

        $content = $extractor->getContent($file);
        $this->assertStringContainsString('This is a test file with a link', $content);

        // Check mime validation
        $this->assertTrue($extractor->supportsMime('application/pdf'));
        $this->assertTrue($extractor->supportsMime('text/html'));
        $this->assertFalse($extractor->supportsMime('application/not-supported'));
    }
}
