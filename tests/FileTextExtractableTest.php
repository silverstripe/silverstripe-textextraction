<?php
class FileTextExtractableTest extends SapphireTest {

	protected $requiredExtensions = array(
		'File' => array('FileTextExtractable')
	);

	function testExtractFileAsText() {
		// Create a copy of the file, as it may be clobbered by the test
		// ($file->extractFileAsText() calls $file->write)
		copy(BASE_PATH.'/textextraction/tests/fixtures/test1.html',BASE_PATH.'/textextraction/tests/fixtures/test1-copy.html');
		
		// Use HTML, since the extractor is always available
		$file = new File(array(
			'Name' => 'test1-copy.html',
			'Filename' => 'textextraction/tests/fixtures/test1-copy.html'
		));
		$file->write();
	
		$content = $file->extractFileAsText();
		$this->assertContains('Test Headline', $content);
		$this->assertContains('Test Text', $content);
		$this->assertEquals($content, $file->FileContentCache);

		if(file_exists(BASE_PATH.'/textextraction/tests/fixtures/test1-copy.html')) unlink(BASE_PATH.'/textextraction/tests/fixtures/test1-copy.html');
	}


}