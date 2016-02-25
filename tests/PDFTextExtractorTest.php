<?php
class PDFTextExtractorTest extends SapphireTest
{
    public function testExtraction()
    {
        $extractor = new PDFTextExtractor();
        if (!$extractor->isAvailable()) {
            $this->setExpectedException(
                'FileTextExtractor_Exception',
                'getRawOutput called on unavailable extractor'
            );
        }

        $content = $extractor->getContent(Director::baseFolder() . '/textextraction/tests/fixtures/test1.pdf');
        $this->assertContains('This is a test file with a link', $content);
    }
}
