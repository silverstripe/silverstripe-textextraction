<?php

/**
 * Enables text extraction of file content via the Tika CLI
 * 
 * {@link http://tika.apache.org/1.7/gettingstarted.html}
 */
class TikaTextExtractor extends FileTextExtractor {

	/**
	 * Text extraction mode. Defaults to -t (plain text)
	 *
	 * @var string
	 * @config
	 */
	private static $output_mode = '-t';

	/**
	 * Get the version of tika installed, or 0 if not installed
	 *
	 * @return float version of tika
	 */
	public function getVersion() {
		$code = $this->runShell('tika --version', $stdout);

		// Parse output
		if(!$code && preg_match('/Apache Tika (?<version>[\.\d]+)/', $stdout, $matches)) {
			return $matches['version'];
		}

		return 0;
	}

	/**
	 * Runs an arbitrary and safely escaped shell command
	 *
	 * @param string $command Full command including arguments
	 * @param string &$stdout Standand output
	 * @param string &$stderr Standard error
	 * @param string $input Content to pass via standard input
	 * @return int Exit code. 0 is success
	 */
	protected function runShell($command, &$stdout = '', &$stderr = '', $input = '') {
		$descriptorSpecs = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);
		// Invoke command
		$pipes = array();
		$proc = proc_open($command, $descriptorSpecs, $pipes);
		if (!is_resource($proc)) return 255;

		// Send content as input
		fwrite($pipes[0], $input);
		fclose($pipes[0]);

		// Get output
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		// Get result
		return proc_close($proc);
	}
	
	public function getContent($path) {
		$mode = $this->config()->output_mode;
		$command = sprintf('tika %s %s', $mode, escapeshellarg($path));
		$code = $this->runShell($command, $output);
		if($code == 0) return $output;
	}

	public function isAvailable() {
		return $this->getVersion() > 0;
	}

	public function supportsExtension($extension) {
		// Determine support via mime type only
		return false;
	}

	public function supportsMime($mime) {
		// Get list of supported mime types
		$code = $this->runShell('tika --list-supported-types', $supportedTypes, $error);
		if($code) return false; // Error case

		// Check if the mime type is inside the result
		$pattern = sprintf('/\b(%s)\b/', preg_quote($mime, '/'));
		return (bool)preg_match($pattern, $supportedTypes);
	}

}
