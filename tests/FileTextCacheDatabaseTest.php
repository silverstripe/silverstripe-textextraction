<?php

namespace SilverStripe\TextExtraction\Tests;

use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\TextExtraction\Cache\FileTextCache\Database;

class FileTextCacheDatabaseTest extends SapphireTest
{
    public function testTruncatesByMaxLength()
    {
        Config::modify()->set(Database::class, 'max_content_length', 5);

        $cache = new Database();
        $file = $this->getMockBuilder(File::class)->setMethods(['write'])->getMock();
        $content = '0123456789';
        $cache->save($file, $content);

        $this->assertEquals($cache->load($file), '01234');
    }
}
