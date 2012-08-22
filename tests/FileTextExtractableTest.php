<?php
class FileTextExtractableTest extends SapphireTest {

	protected $requiredExtensions = array(
		'File' => array('FileTextExtractable')
	);

	function testExtractFileAsText() {
		// Use HTML, since the extractor is always available
		$file = new File(array(
			'Name' => 'test1.html',
			'Filename' => 'textextraction/tests/fixtures/test1.html'
		));
		$file->write();

		$content = $file->extractFileAsText();
		$this->assertContains('Test Headline', $content);
		$this->assertContains('Test Text', $content);
		$this->assertEquals($content, $file->FileContentCache);
	}


}