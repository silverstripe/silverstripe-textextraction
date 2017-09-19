<?php

use Guzzle\Http\Client;
use Guzzle\Http\Exception\RequestException;

class TikaRestClient extends Client
{
    /*
    * @var Array
    */
    private static $_options = ['username' => null, 'password' => null];

    /*
    * @var Boolean
    */
    private static $_available = false;

    /*
    * @var Float
    */
    private static $_version = 0.0;

    /*
    * @var Array
    */
    protected $mimes = [];

    public function __construct($baseUrl = '', $config = null)
    {
        if (defined('SS_TIKA_USERNAME') && defined('SS_TIKA_PASSWORD')) {
            self::$_options = [
                'username' => SS_TIKA_USERNAME,
                'password' => SS_TIKA_PASSWORD,
            ];
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
        if (!self::$_available) {
            try {
                $result = $this
                    ->get(null);
                $result->setAuth(self::$_options['username'], self::$_options['password']);
                $result->send();
                if ($result->getResponse()->getStatusCode() == 200) {
                    self::$_available = true;
                }
            } catch (RequestException $ex) {
                self::$_available = false;
            }
        }
        return self::$_available;
    }

    /**
     * Get version code
     *
     * @return float
     */
    public function getVersion()
    {
        if (self::$_version == 0.0) {
            $response = $this->get('version');
            $response->setAuth(self::$_options['username'], self::$_options['password']);
            $response->send();
            // Parse output
            if ($response->getResponse()->getStatusCode() == 200 &&
                preg_match('/Apache Tika (?<version>[\.\d]+)/', $response->getResponse()->getBody(), $matches)
            ) {
                self::$_version = (float)$matches['version'];
            } else {
                self::$_version = 0.0;
            }
        }
        return self::$_version;
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
            ['Accept' => 'application/json']
        );
        $response->setAuth(self::$_options['username'], self::$_options['password']);
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
                ['Accept' => 'text/plain'],
                file_get_contents($file)
            );
            $response->setAuth(self::$_options['username'], self::$_options['password']);
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
