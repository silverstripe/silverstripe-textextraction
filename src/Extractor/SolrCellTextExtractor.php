<?php

namespace SilverStripe\TextExtraction\Extractor;

use SilverStripe\TextExtraction\Extractor\FileTextExtractor,
    GuzzleHttp\Client,
    Psr\Log\LoggerInterface;

/**
 * Text extractor that calls an Apache Solr instance
 * and extracts content via the "ExtractingRequestHandler" endpoint.
 * Does not alter the Solr index itself, but uses it purely
 * for its file parsing abilities.
 *
 * @author ischommer
 * @see  http://wiki.apache.org/solr/ExtractingRequestHandler
 */
class SolrCellTextExtractor extends FileTextExtractor
{
    /**
     * Base URL to use for solr text extraction.
     * E.g. http://localhost:8983/solr/update/extract
     *
     * @config
     * @var string
     */
    private static $base_url;

    /**
     *
     * @var int
     * @config
     */
    private static $priority = 75;

    /**
     *
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     *
     * @return GuzzleHttp\Client
     * @throws InvalidArgumentException
     */
    public function getHttpClient()
    {
        if (!$this->config()->get('base_url')) {
            throw new \InvalidArgumentException('SolrCellTextExtractor.base_url not specified');
        }
        if (!$this->httpClient) {
            $this->httpClient = new Client($this->config()->get('base_url'));
        }

        return $this->httpClient;
    }

    /**
     *
     * @param  GuzzleHttp\Client $client
     * @return void
     */
    public function setHttpClient($client)
    {
        $this->httpClient = $client;
    }

    /**
     * @return string
     */
    public function isAvailable()
    {
        $url = $this->config()->get('base_url');

        return (boolean) $url;
    }

    /**
     *
     * @param  string $extension
     * @return boolean
     */
    public function supportsExtension($extension)
    {
        return in_array(
            strtolower($extension),
            array(
                'pdf', 'doc', 'docx', 'xls', 'xlsx',
                'epub', 'rtf', 'odt', 'fodt', 'ods', 'fods',
                'ppt', 'pptx', 'odp', 'fodp', 'csv'
            )
        );
    }

    /**
     *
     * @param  string $mime
     * @return boolean
     */
    public function supportsMime($mime)
    {
        // Rely on supportsExtension
        return false;
    }

    /**
     *
     * @param  string $path
     * @return string
     */
    public function getContent($path)
    {
        if (!$path) {
            return "";
        } // no file

        $fileName = basename($path);
        $client = $this->getHttpClient();

        try {
            $request = $client
                ->post()
                ->addPostFields(array('extractOnly' => 'true', 'extractFormat' => 'text'))
                ->addPostFiles(array('myfile' => $path));
            $response = $request->send();
        } catch (\InvalidArgumentException $e) {
            $msg = sprintf(
                    'Error extracting text from "%s" (message: %s)',
                    $path,
                    $e->getMessage()
                );
            Injector::inst()->get(LoggerInterface::class)->notice($msg);

            return null;
        } catch (\Exception $e) {
            // Catch other errors that Tika can throw vai Guzzle but are not caught and break Solr search query in some cases.
            $msg = sprintf(
                    'Tika server error attempting to extract from "%s" (message: %s)',
                    $path,
                    $e->getMessage()
                );

            Injector::inst()->get(LoggerInterface::class)->notice($msg);

            return null;
        }

        // Just initialise it, it doesn't take miuch.
        $matches = [];

        // Use preg match to avoid SimpleXML running out of memory on large text nodes
        preg_match(
            sprintf('/\<str name\="%s"\>(.*?)\<\/str\>/s', preg_quote($fileName)),
            (string)$response->getBody(),
            $matches
        );

        return $matches ? $matches[1] : null;
    }
}
