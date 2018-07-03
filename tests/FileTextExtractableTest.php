<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Extension\FileTextExtractable;

class FileTextExtractableTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $required_extensions = [
        File::class => [
            FileTextExtractable::class,
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        // Ensure that html is a valid extension
        Config::modify()->merge(File::class, 'allowed_extensions', ['html']);

        // Create a copy of the file, as it may be clobbered by the test
        // ($file->extractFileAsText() calls $file->write)
        copy(
            dirname(__FILE__) . '/fixtures/test1.html',
            dirname(__FILE__) . '/fixtures/test1-copy.html'
        );
    }

    protected function tearDown()
    {
        if (file_exists(dirname(__FILE__) . '/fixtures/test1-copy.html')) {
            unlink(dirname(__FILE__) . '/fixtures/test1-copy.html');
        }

        parent::tearDown();
    }

    public function testExtractFileAsText()
    {
        // Use HTML, since the extractor is always available
        /** @var File|FileTextExtractable $file */
        $file = new File(['Name' => 'test1-copy.html']);
        $file->setFromLocalFile(dirname(__FILE__) . '/fixtures/test1-copy.html');
        $file->write();

        $content = $file->extractFileAsText();
        $this->assertNotNull($content);
        $this->assertContains('Test Headline', $content);
        $this->assertContains('Test Text', $content);
        $this->assertEquals($content, $file->FileContentCache);
    }
}
