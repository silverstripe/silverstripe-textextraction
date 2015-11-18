<?php

use Guzzle\Http\Client;
use Guzzle\Http\Exception\RequestException;

class TikaRestClient extends Client
{
    /**
     * Detect if the service is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        try {
            return $this
                ->get()->send()
                ->getStatusCode() == 200;
        } catch (RequestException $ex) {
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
        $response = $this->get('version')->send();
        // Parse output
        if ($response->getStatusCode() == 200 &&
            preg_match('/Apache Tika (?<version>[\.\d]+)/', $response->getBody(), $matches)
        ) {
            return (float)$matches['version'];
        }

        return 0.0;
    }

    protected $mimes = array();

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
        )->send();

        return $this->mimes = $response->json();
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
            )->send();
            $text = $response->getBody(true);
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
