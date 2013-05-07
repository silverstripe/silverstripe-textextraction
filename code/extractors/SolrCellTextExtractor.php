<?php
use Guzzle\Http\Client;

/**
 * Text extractor that calls an Apache Solr instance
 * and extracts content via the "ExtractingRequestHandler" endpoint.
 * Does not alter the Solr index itself, but uses it purely
 * for its file parsing abilities.
 * 
 * @author ischommer
 * @see  http://wiki.apache.org/solr/ExtractingRequestHandler
 */
class SolrCellTextExtractor extends FileTextExtractor {

	/**
	 * @config
	 * @var [type]
	 */
	private static $base_url;

	private static $priority = 75;

	protected $httpClient;

	public function getHttpClient() {
		if(!$this->httpClient) $this->httpClient = new Client($this->config()->get('base_url'));
		return $this->httpClient;
	}

	public function setHttpClient($client) {
		$this->httpClient = $client;
	}

	public function isAvailable() {
		$url = $this->config()->get('base_url');
		if(!$url) return false;
	}
	
	/**
	 * @see  http://tika.apache.org/1.3/formats.html
	 * @return Array
	 */
	public function supportedExtensions() {
		return array(
			'pdf', 'doc', 'docx', 'xls', 'xlsx',
			'epub', 'rtf', 'odt', 'fodt', 'ods', 'fods',
			'ppt', 'pptx', 'odp', 'fodp', 'csv'
		);
	}
	
	public function getContent($path) {
		if (!$path) return ""; // no file
		
		$fileName = basename($path);
		$client = $this->getHttpClient();
		$request = $client
			->post('?extractOnly=true&extractFormat=text')
			->addPostFiles(array('myfile' => $path));
		$response = $request->send();
		// Use preg match to avoid SimpleXML running out of memory on large text nodes
		preg_match(
			sprintf('/\<str name\="%s"\>(.*?)\<\/str\>/s', preg_quote($fileName)),
			(string)$response->getBody(), 
			$matches
		);
		return $matches ? $matches[1] : null;
	}
}