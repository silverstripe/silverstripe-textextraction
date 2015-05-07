<?php
class FileTextCacheDatabaseTest extends SapphireTest {
	
	public function testTruncatesByMaxLength() {
		Config::nest();
		
		Config::inst()->update('FileTextCache_Database', 'max_content_length', 5);
		$cache = new FileTextCache_Database();
		$file = $this->getMock('File', array('write'));
		$content = '0123456789';
		$cache->save($file, $content);
		$this->assertEquals($cache->load($file), '01234');

		Config::unnest();
	}

}