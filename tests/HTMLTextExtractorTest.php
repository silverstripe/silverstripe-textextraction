<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\HTMLTextExtractor;

class HTMLTextExtractorTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function setUp()
    {
        parent::setUp();

        Config::modify()->merge(File::class, 'allowed_extensions', ['html']);
    }

    public function testExtraction()
    {
        $extractor = new HTMLTextExtractor();

        $file = new File();
        $file->setFromLocalFile(dirname(__FILE__) . '/fixtures/test1.html');
        $file->write();

        $content = $extractor->getContent($file);

        $this->assertContains('Test Headline', $content);
        $this->assertNotContains('Test Comment', $content, 'Strips HTML comments');
        $this->assertNotContains('Test Style', $content, 'Strips non-content style tags');
        $this->assertNotContains('Test Script', $content, 'Strips non-content script tags');
    }
}
