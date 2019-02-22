<?php

namespace SilverStripe\TextExtraction\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Assets\File;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extractor\TikaServerTextExtractor;
use SilverStripe\TextExtraction\Rest\TikaRestClient;

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

    /**
     * @param string $version
     * @param bool $expected
     * @dataProvider isAvailableProvider
     */
    public function testIsAvailable($version, $expected)
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|TikaServerTextExtractor $extractor */
        $extractor = $this->getMockBuilder(TikaServerTextExtractor::class)
            ->setMethods(['getClient', 'getServerEndpoint'])
            ->getMock();

        $client = $this->createMock(TikaRestClient::class);
        $client->method('isAvailable')->willReturn(true);
        $client->method('getVersion')->willReturn($version);

        $extractor->method('getClient')->willReturn($client);
        $extractor->method('getServerEndpoint')->willReturn('tikaserver.example');

        $result = $extractor->isAvailable();
        $this->assertSame($expected, $result);
    }

    /**
     * @return array[]
     */
    public function isAvailableProvider()
    {
        return [
            ['1.5.2', false],
            ['1.5', false],
            ['1.7.0', true],
            ['1.7.5', true],
            ['1.8.0', true],
            ['1.7', true],
            ['1.8', true],
            ['2.0.0', true],
        ];
    }
}
