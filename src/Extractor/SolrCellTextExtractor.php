<?php

namespace SilverStripe\TextExtraction\Extractor;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SilverStripe\Assets\File;
use SilverStripe\Core\Injector\Injector;

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
     * Base URL to use for Solr text extraction.
     * E.g. http://localhost:8983/solr/update/extract
     *
     * @config
     * @var string
     */
    private static $base_url;

    /**
     * @var int
     * @config
     */
    private static $priority = 75;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        if (!$this->httpClient) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    /**
     * @param  Client $client
     * @return $this
     */
    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function isAvailable()
    {
        $url = $this->config()->get('base_url');

        return (bool) $url;
    }

    /**
     * @param  string $extension
     * @return bool
     */
    public function supportsExtension($extension)
    {
        return in_array(
            strtolower($extension ?? ''),
            [
                'pdf', 'doc', 'docx', 'xls', 'xlsx',
                'epub', 'rtf', 'odt', 'fodt', 'ods', 'fods',
                'ppt', 'pptx', 'odp', 'fodp', 'csv'
            ]
        );
    }

    /**
     * @param  string $mime
     * @return bool
     */
    public function supportsMime($mime)
    {
        // Rely on supportsExtension
        return false;
    }

    /**
     * @param File|string $file
     * @return string
     * @throws InvalidArgumentException
     */
    public function getContent($file)
    {
        if (!$file || (is_string($file) && !file_exists($file ?? ''))) {
            // no file
            return '';
        }

        $fileName = $file instanceof File ? $file->getFilename() : basename($file ?? '');
        $client = $this->getHttpClient();

        // Get and validate base URL
        $baseUrl = $this->config()->get('base_url');
        if (!$this->config()->get('base_url')) {
            throw new InvalidArgumentException('SolrCellTextExtractor.base_url not specified');
        }

        try {
            $stream = $file instanceof File ? $file->getStream() : fopen($file ?? '', 'r');
            /** @var Response $response */
            $response = $client
                ->post($baseUrl, [
                    'multipart' => [
                        ['name' => 'extractOnly', 'contents' => 'true'],
                        ['name' => 'extractFormat', 'contents' => 'text'],
                        ['name' => 'myfile', 'contents' => $stream],
                    ]
                ]);
        } catch (InvalidArgumentException $e) {
            $msg = sprintf(
                'Error extracting text from "%s" (message: %s)',
                $fileName,
                $e->getMessage()
            );
            Injector::inst()->get(LoggerInterface::class)->notice($msg);
            return null;
        } catch (Exception $e) {
            // Catch other errors that Tika can throw via Guzzle but are not caught and break Solr search
            // query in some cases.
            $msg = sprintf(
                'Tika server error attempting to extract from "%s" (message: %s)',
                $fileName,
                $e->getMessage()
            );
            Injector::inst()->get(LoggerInterface::class)->notice($msg);
            return null;
        }

        $matches = [];
        // Use preg match to avoid SimpleXML running out of memory on large text nodes
        preg_match(
            sprintf('/\<str name\="%s"\>(.*?)\<\/str\>/s', preg_quote($fileName ?? '')),
            (string)$response->getBody(),
            $matches
        );

        return $matches ? $matches[1] : null;
    }
}
