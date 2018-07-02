<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\HTMLTextExtractor;

class HTMLTextExtractorTest extends SapphireTest
{
    public function testExtraction()
    {
        $extractor = new HTMLTextExtractor();

        $content = $extractor->getContent(dirname(__FILE__) . '/fixtures/test1.html');

        $this->assertContains('Test Headline', $content);
        $this->assertNotContains('Test Comment', $content, 'Strips HTML comments');
        $this->assertNotContains('Test Style', $content, 'Strips non-content style tags');
        $this->assertNotContains('Test Script', $content, 'Strips non-content script tags');
    }
}
