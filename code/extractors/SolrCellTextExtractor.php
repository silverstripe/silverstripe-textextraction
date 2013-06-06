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
		if(!$this->config()->get('base_url')) {
			throw new InvalidArgumentException('SolrCellTextExtractor.base_url not specified');
		}
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
		try {
			$request = $client
				->post()
				->addPostFields(array('extractOnly' => 'true', 'extractFormat' => 'text'))
				->addPostFiles(array('myfile' => $path));
			$response = $request->send();
		} catch(InvalidArgumentException $e) {
			SS_Log::log(
				sprintf(
					'Error extracting text from "%s" (message: %s)', 
					$path, 
					$e->getMessage()
				),
				SS_Log::NOTICE
			);
			return null;
		} catch(Guzzle\Http\Exception\ServerErrorResponseException $e){
			//catch other errors that Tika can throw vai Guzzle but are not caught and break Solr search query in some cases.
			SS_Log::log(
				sprintf(
					'Tika server error attempting to extract from "%s" (message: %s)', 
					$path, 
					$e->getMessage()
				),
				SS_Log::NOTICE
			);
			return null;
		}

		// Use preg match to avoid SimpleXML running out of memory on large text nodes
		preg_match(
			sprintf('/\<str name\="%s"\>(.*?)\<\/str\>/s', preg_quote($fileName)),
			(string)$response->getBody(), 
			$matches
		);

		return $matches ? $matches[1] : null;
	}
}
