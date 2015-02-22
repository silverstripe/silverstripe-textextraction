<?php

/**
 * Text extractor that calls pdftotext to do the conversion.
 * @author mstephens
 *
 */
class PDFTextExtractor extends FileTextExtractor {

	public function isAvailable() {
		$bin = $this->bin('pdftotext');
		return (file_exists($bin) && is_executable($bin));
	}
	
	public function supportsExtension($extension) {
		return strtolower($extension) === 'pdf';
	}

	public function supportsMime($mime) {
		return in_array(
			strtolower($mime),
			array(
				'application/pdf',
				'application/x-pdf',
				'application/x-bzpdf',
				'application/x-gzpdf'
			)
		);
	}

	/**
	 * Accessor to get the location of the binary
	 *
	 * @param string $prog Name of binary
	 * @return string
	 */
	protected function bin($prog = '') {
		if ($this->config()->binary_location) {
			// By config
			$path = $this->config()->binary_location;
		} elseif (file_exists('/usr/bin/pdftotext')) {
			// By searching common directories
			$path = '/usr/bin';
		} elseif (file_exists('/usr/local/bin/pdftotext')) {
			$path = '/usr/local/bin';
		} else {
			$path = '.'; // Hope it's in path
		}

		return ( $path ? $path . '/' : '' ) . $prog;
	}
	
	public function getContent($path) {
		if(!$path) return ""; // no file
		$content = $this->getRawOutput($path);
		return $this->cleanupLigatures($content);
	}

	/**
	 * Invoke pdftotext with the given path
	 *
	 * @param string $path
	 * @return string Output
	 * @throws FileTextExtractor_Exception
	 */
	protected function getRawOutput($path) {
		exec(sprintf('%s %s - 2>&1', $this->bin('pdftotext'), escapeshellarg($path)), $content, $err);
		if($err) {
			throw new FileTextExtractor_Exception(sprintf(
				'PDFTextExtractor->getContent() failed for %s: %s',
				$path,
				implode('', $err)
			));
		}
		return implode('', $content);
	}

	/**
	 * Removes utf-8 ligatures.
	 *
	 * @link http://en.wikipedia.org/wiki/Typographic_ligature#Computer_typesetting
	 *
	 * @param string $input
	 * @return string
	 */
	protected function cleanupLigatures($input) {
		$mapping = array(
			'ﬀ' => 'ff',
			'ﬁ' => 'fi',
			'ﬂ' => 'fl',
			'ﬃ' => 'ffi',
			'ﬄ' => 'ffl',
			'ﬅ' => 'ft',
			'ﬆ' => 'st'
		);
		return str_replace(array_keys($mapping), array_values($mapping), $input);
	}
}
