<?php
class HTMLTextExtractorTest extends SapphireTest {
	
	function testExtraction() {
		$extractor = new HTMLTextExtractor();

		$content = $extractor->getContent(Director::baseFolder() . '/textextraction/tests/fixtures/test1.html');
		$this->assertContains('Test Headline', $content);
	}

}