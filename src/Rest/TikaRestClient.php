<?php

namespace SilverStripe\TextExtraction\Rest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;

class TikaRestClient extends Client
{
    /**
     * Authentication options to be sent to the Tika server
     *
     * @var array
     */
    protected $options = ['username' => null, 'password' => null];

    /**
     * @var array
     */
    protected $mimes = [];

    /**
     *
     * @param string $baseUrl
     * @param array $config
     */
    public function __construct($baseUrl = '', $config = [])
    {
        $password = Environment::getEnv('SS_TIKA_PASSWORD');

        if (!empty($password)) {
            $this->options = [
                'username' => Environment::getEnv('SS_TIKA_USERNAME'),
                'password' => $password,
            ];
        }

        $config['base_uri'] = $baseUrl;

        parent::__construct($config);
    }

    /**
     * Detect if the service is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        try {
            /** @var Response $result */
            $result = $this->get('/', $this->getGuzzleOptions());

            if ($result->getStatusCode() == 200) {
                return true;
            }
        } catch (RequestException $ex) {
            $msg = sprintf("Tika unavailable - %s", $ex->getMessage());
            Injector::inst()->get(LoggerInterface::class)->info($msg);

            return false;
        }
    }

    /**
     * Get version code
     *
     * @return string
     */
    public function getVersion()
    {
        /** @var Response $response */
        $response = $this->get('version', $this->getGuzzleOptions());
        $version = 0;

        // Parse output
        if ($response->getStatusCode() == 200
            && preg_match('/Apache Tika (?<version>[\.\d]+)/', $response->getBody() ?? '', $matches)
        ) {
            $version = $matches['version'];
        }

        return (string) $version;
    }

    /**
     * Gets supported mime data. May include aliased mime types.
     *
     * @return array
     */
    public function getSupportedMimes()
    {
        if ($this->mimes) {
            return $this->mimes;
        }

        $response = $this->get(
            'mime-types',
            $this->getGuzzleOptions([
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ])
        );

        return $this->mimes = json_decode($response->getBody(), true);
    }

    /**
     * Extract text content from a given file.
     * Logs a notice-level error if the document can't be parsed.
     *
     * @param  string $file Full filesystem path to a file to post
     * @return string Content of the file extracted as plain text
     */
    public function tika($file)
    {
        $text = null;
        try {
            /** @var Response $response */
            $response = $this->put(
                'tika',
                $this->getGuzzleOptions([
                    'headers' => [
                        'Accept' => 'text/plain',
                    ],
                    'body' => file_get_contents($file ?? ''),
                ])
            );
            $text = $response->getBody();
        } catch (RequestException $e) {
            $msg = sprintf(
                'TikaRestClient was not able to process %s. Response: %s %s.',
                $file,
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getReasonPhrase()
            );
            // Only available if tika-server was started with --includeStack
            $body = $e->getResponse()->getBody();
            if ($body) {
                $msg .= ' Body: ' . $body;
            }

            Injector::inst()->get(LoggerInterface::class)->info($msg);
        }

        return (string) $text;
    }

    /**
     * Assembles an array of request options to pass to Guzzle
     *
     * @param array $options Authentication (etc) will be merged into this array and returned
     * @return array
     */
    protected function getGuzzleOptions($options = [])
    {
        if (!empty($this->options['username']) && !empty($this->options['password'])) {
            $options['auth'] = [
                $this->options['username'],
                $this->options['password']
            ];
        }
        return $options;
    }
}
