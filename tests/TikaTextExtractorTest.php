<?php

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
        $file = Director::baseFolder() . '/textextraction/tests/fixtures/test1.pdf';
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
        $file = Director::baseFolder() . '/textextraction/tests/fixtures/test1.pdf';
        $content = $extractor->getContent($file);
        $this->assertContains('This is a test file with a link', $content);

        // Check mime validation
        $this->assertTrue($extractor->supportsMime('application/pdf'));
        $this->assertTrue($extractor->supportsMime('text/html'));
        $this->assertFalse($extractor->supportsMime('application/not-supported'));
    }
    
    public function testNormaliseVersion()
    {
        $extractor = new TikaServerTextExtractor();
        $reflection = new ReflectionClass($extractor);
        $method = $reflection->getMethod('normaliseVersion');
        $method->setAccessible(true);

        foreach ($this->versionProvider() as $data) {
            list($input, $expected) = $data;
            $actual = $method->invoke($extractor, $input);
            $this->assertEquals($expected, $actual);
        }
    }
    
    protected function versionProvider()
    {
        return [
            ['1.7.1', '1.7.1'],
            ['1.7', '1.7.0'],
            ['1', '1.0.0'],
            [null, '0.0.0'],
            ['v1.5', 'v1.5.0'],
            ['carrot', 'carrot.0.0']
        ];
    }
}
