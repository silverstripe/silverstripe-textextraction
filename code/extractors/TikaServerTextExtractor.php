<?php

/**
 * Enables text extraction of file content via the Tika Rest Server
 *
 * {@link http://tika.apache.org/1.7/gettingstarted.html}
 */
class TikaServerTextExtractor extends FileTextExtractor
{
    /**
     * Tika server is pretty efficient so use it immediately if available
     *
     * @var integer
     * @config
     */
    private static $priority = 80;

    /**
     * Server endpoint
     *
     * @var string
     * @config
     */
    private static $server_endpoint;

    /**
     * @var TikaRestClient
     */
    protected $client = null;

    /**
     * @return TikaRestClient
     */
    public function getClient()
    {
        return $this->client ?:
            ($this->client =
                Injector::inst()->createWithArgs(
                    'TikaRestClient',
                    array($this->getServerEndpoint())
                )
            );
    }

    public function getServerEndpoint()
    {
        if (defined('SS_TIKA_ENDPOINT')) {
            return SS_TIKA_ENDPOINT;
        }

        if (getenv('SS_TIKA_ENDPOINT')) {
            return getenv('SS_TIKA_ENDPOINT');
        }

        // Default to configured endpoint
        return $this->config()->server_endpoint;
    }

    /**
     * Get the version of tika installed, or 0 if not installed
     *
     * @return float version of tika
     */
    public function getVersion()
    {
        return $this
            ->getClient()
            ->getVersion();
    }

    public function isAvailable()
    {
        return $this->getServerEndpoint() &&
            $this->getClient()->isAvailable() &&
            version_compare($this->getVersion(), '1.7.0') >= 0;
    }

    public function supportsExtension($extension)
    {
        // Determine support via mime type only
        return false;
    }


    /**
     * Cache of supported mime types
     *
     * @var array
     */
    protected $supportedMimes = array();

    public function supportsMime($mime)
    {
        $supported = $this->supportedMimes ?:
            ($this->supportedMimes = $this->getClient()->getSupportedMimes());

        // Check if supported (most common / quickest lookup)
        if (isset($supported[$mime])) {
            return true;
        }

        // Check aliases
        foreach ($supported as $info) {
            if (isset($info['alias']) && in_array($mime, $info['alias'])) {
                return true;
            }
        }

        return false;
    }

    public function getContent($path)
    {
        return $this->getClient()->tika($path);
    }
}
