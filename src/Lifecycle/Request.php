<?php

namespace JasperFW\JasperFW\Lifecycle;

use JasperFW\JasperFW\Jasper;

/**
 * Class Request
 *
 * A request object represents the request either recieved by the application via the web server, or through the command
 * line interface.
 *
 * @package JasperFW\JasperFW\Lifecycle
 */
class Request
{
    /** @var string The HTTP method of the request. For CLI requests, this will be "CLI" */
    protected $method;
    /** @var string The requested URI */
    protected $uri;
    /** @var array The query parameters */
    protected $query;
    /** @var string|bool The content type of the request body */
    protected $contentType;
    /** @var string The raw request body */
    protected $requestBody_raw;
    /** @var array The request body, parsed based on the content-type */
    protected $requestBody = [];
    /** @var mixed The post contents */
    protected $post;
    /** @var string The base directory - use if the framework is not in the root of the domain */
    protected $baseDirectory;
    /** @var string The locale as determined from the request URI */
    protected $locale;
    /** @var string The IP address of the remote user. Attempts to resolve original IP address if there are proxies */
    protected $remoteIP;
    /** @var string The raw IP address of the remote user. This is the IP address passed by the server. */
    protected $rawRemoteIP;
    /** @var string[] Array of uri pieces */
    protected $uriPieces;
    /** @var string The name of the file being requested */
    protected $filename;
    /** @var string The extension of the file being requested */
    protected $extension;
    /**@var string The request URI as a path that can be processed by the router */
    protected $path;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->contentType = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : false;
        $this->requestBody_raw = file_get_contents("php://input");
        $this->parseRequestBody();
        $this->query = $_GET;
        $this->post = $_POST;
        $this->determineRemoteIP();
        $this->baseDirectory = Jasper::i()->config->getConfiguration('framework')['base'] ?? '';
        $this->processURI($this->uri);
        $this->path = implode('/', $this->uriPieces);
    }

    /**
     * Get the request method.
     *
     * @return string The request method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the URI. This is public so that other systems can change the URI to do internal redirects.
     *
     * @param string $uri The new URI
     */
    public function setURI(string $uri): void
    {
        $this->uri = $uri;
        $this->processURI($this->uri);
    }

    /**
     * Get the request URI
     *
     * @return string The request URI
     */
    public function getURI(): string
    {
        return $this->uri;
    }

    /**
     * Get the array of URI pieces
     *
     * @return string[] The array of URI pieces
     */
    public function getUriPieces(): array
    {
        return $this->uriPieces;
    }

    /**
     * Get the name of the file being requested. This is the raw name, with the extension removed.
     *
     * @return string The name of the file
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get the extension of the file being requested. If there is no extension this will return an empty string.
     *
     * @return string The extension of the file being requested
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Get the request path
     *
     * @return string The request path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get an array of query string elements
     *
     * @return array The array of query string elements
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Get the post data
     *
     * @return mixed The post data
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set the localse to a valid locale code
     */
    /**
     * @param string $locale The new locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Get the locale string as determined from the request
     *
     * @return string|null The locale string or null if no locale is set
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Return the IP address of the remote client. This attempts to backtrace through any non-anonymizing proxies.
     * <p>
     * Warning! This value is easily spoofed. If logging IP addresses, it is highly recommended to also log the
     * RawRemoteIP unless a proxy server is being used for the site, such as a load balancer.
     *
     * @return string The remote IP address
     */
    public function getRemoteIP(): string
    {
        return $this->remoteIP;
    }

    public function getRawRemoteIP(): string
    {
        return $this->rawRemoteIP;
    }

    /**
     * Return true if the remote connection is over SSL
     *
     * @return bool True if the connection is HTTPS, false otherwise
     */
    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * Splits the URL into an array
     *
     * @param string $url The URL to be processed
     */
    protected function processURI(string $url): void
    {
        // Remove the base folder if one is set
        if ($this->baseDirectory !== '') {
            $bd = '/' . $this->baseDirectory;
            if (substr($url, 0, strlen($bd)) == $bd) {
                $url = substr($url, strlen($bd));
            }
        }
        // Remove query string if it is set
        $url = explode('?', $url)[0];
        // Remvoe the leading slash
        $url = trim($url, '/');
        // Split the URL by the slashes
        $url_array = explode('/', $url);
        $this->extractLocale($url_array);
        $filename = array_pop($url_array);
        $this->extension = pathinfo($filename, PATHINFO_EXTENSION);
        $url_array[] = $this->filename = pathinfo($filename, PATHINFO_FILENAME);
        $this->uriPieces = $url_array;
    }

    /**
     * Remove the locale from the first element in the array if one is set.
     *
     * @param array $url_array
     */
    protected function extractLocale(array &$url_array): void
    {
        if (count($url_array) > 0 && preg_match(
                '/^([a-z0-9]{2,3})-([a-z0-9]{4}-?)?([a-z0-9]{2,3})?$/i',
                $url_array[0]
            )) {
            $this->setLocale(array_shift($url_array));
        }
        reset($url_array);
    }

    /**
     * Get the IP address of the user
     */
    private function determineRemoteIP(): void
    {
        $this->rawRemoteIP = $ip = $_SERVER['REMOTE_ADDR'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ip = explode(',', $ip);
            $ip = trim($ip[0]);
        }
        $this->remoteIP = $ip;
    }

    /**
     * Based on the content type, parse the request body
     *
     * TODO: Replace this with a call to call_user_func that checks an array that can be set in configuration.
     */
    protected function parseRequestBody()
    {
        switch ($this->contentType) {
            case 'application/x-www-form-urlencoded':
                parse_str($this->requestBody_raw, $postVars);
                foreach ($postVars as $field => $value) {
                    $this->requestBody[$field] = $value;
                }
                return;
            case 'application/json':
                $body_params = json_decode($this->requestBody_raw);
                if ($body_params) {
                    foreach ($body_params as $field => $value) {
                        $this->requestBody[$field] = $value;
                    }
                }
                return;
            default:
                $this->requestBody = $_POST;
        }
    }
}
