<?php
class HTMLTextExtractorTest extends SapphireTest {
	
	function testExtraction() {
		$extractor = new HTMLTextExtractor();

		$content = $extractor->getContent(Director::baseFolder() . '/textextraction/tests/fixtures/test1.html');
		$this->assertContains('Test Headline', $content);
		$this->assertNotContains('Test Comment', $content, 'Strips HTML comments');
		$this->assertNotContains('Test Style', $content, 'Strips non-content style tags');
		$this->assertNotContains('Test Script', $content, 'Strips non-content script tags');
	}

}