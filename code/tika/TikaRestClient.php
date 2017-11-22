<?php

use Guzzle\Http\Client;
use Guzzle\Http\Exception\RequestException;

class TikaRestClient extends Client
{
    /**
     * Authentication options to be sent to the Tika server
     *
     * @var array
     */
    protected $options = array('username' => null, 'password' => null);

    /**
     * @var array
     */
    protected $mimes = array();

    public function __construct($baseUrl = '', $config = null)
    {
        if (defined('SS_TIKA_USERNAME') && defined('SS_TIKA_PASSWORD')) {
            $this->options = array(
                'username' => SS_TIKA_USERNAME,
                'password' => SS_TIKA_PASSWORD,
            );
        }
        parent::__construct($baseUrl, $config);
    }

    /**
     * Detect if the service is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        try {
            $result = $this->get(null);
            $result->setAuth($this->options['username'], $this->options['password']);
            $result->send();
            if ($result->getResponse()->getStatusCode() == 200) {
                return true;
            }
        } catch (RequestException $ex) {
            SS_Log::log(sprintf("Tika unavailable - %s", $ex->getMessage()), SS_Log::ERR);
            return false;
        }
    }

    /**
     * Get version code
     *
     * @return float
     */
    public function getVersion()
    {
        $response = $this->get('version');
        $response->setAuth($this->options['username'], $this->options['password']);
        $response->send();
        $version = 0.0;
        // Parse output
        if ($response->getResponse()->getStatusCode() == 200 &&
            preg_match('/Apache Tika (?<version>[\.\d]+)/', $response->getResponse()->getBody(), $matches)
        ) {
            $version = (float)$matches['version'];
        }
        return $version;
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
            array('Accept' => 'application/json')
        );
        $response->setAuth($this->options['username'], $this->options['password']);
        $response->send();
        return $this->mimes = $response->getResponse()->json();
    }

    /**
     * Extract text content from a given file.
     * Logs a notice-level error if the document can't be parsed.
     *
     * @param string $file Full filesystem path to a file to post
     * @return string Content of the file extracted as plain text
     */
    public function tika($file)
    {
        $text = null;
        try {
            $response = $this->put(
                'tika',
                array('Accept' => 'text/plain'),
                file_get_contents($file)
            );
            $response->setAuth($this->options['username'], $this->options['password']);
            $response->send();
            $text = $response->getResponse()->getBody(true);
        } catch (RequestException $e) {
            $msg = sprintf(
                'TikaRestClient was not able to process %s. Response: %s %s.',
                $file,
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getReasonPhrase()
            );
            // Only available if tika-server was started with --includeStack
            $body = $e->getResponse()->getBody(true);
            if ($body) {
                $msg .= ' Body: ' . $body;
            }
            SS_Log::log($msg, SS_Log::NOTICE);
        }
        return $text;
    }
}
