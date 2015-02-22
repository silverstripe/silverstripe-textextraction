<?php
class PDFTextExtractorTest extends SapphireTest {
	
	function testExtraction() {
		$extractor = new PDFTextExtractor();
		if(!$extractor->isAvailable()) $this->markTestSkipped('pdftotext not available');

		$content = $extractor->getContent(Director::baseFolder() . '/textextraction/tests/fixtures/test1.pdf');
		$this->assertContains('This is a test file with a link', $content);
	}

}
