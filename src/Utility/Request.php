<?php

namespace WigeDev\JasperCore\Utility;

use WigeDev\JasperCore\Core;

/**
 * Class Request
 *
 * A request object represents the request either recieved by the application via the web server, or through the command
 * line interface.
 *
 * @package WigeDev\JasperCore\Utility
 */
class Request
{
    /** @var string The HTTP method of the request. For CLI requests, this will be "CLI" */
    protected $method;
    /** @var string The requested URI */
    protected $uri;
    /** @var array The query parameters */
    protected $query;
    /** @var mixed The post contents */
    protected $post;
    /** @var string The module that the request was routed to */
    protected $module = 'index';
    /** @var string The controller the request was routed to */
    protected $controller = 'index';
    /** @var string The action the request was routed to */
    protected $action = 'index';
    /** @var string The locale as determined from the request URI */
    protected $locale;
    /** @var string The IP address of the remote user. Attempts to resolve original IP address if there are proxies */
    protected $remote_ip;
    /** @var string The raw IP address of the remote user. This is the IP address passed by the server. */
    protected $raw_remote_ip;
    /** @var string[] Array of uri pieces */
    protected $uri_pieces;
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
        if (isset($_REQUEST['request'])) {
            $this->uri = $_REQUEST['request'];
        } else {
            $this->uri = '/';
        }
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->query = $_GET;
        $this->post = $_POST;
        $this->determineRemoteIP();
        $this->uri_pieces = $this->processURI($this->uri);
        $this->path = implode('/', $this->uri_pieces);
        Core::i()->log->debug('Resolved Path: ' . $this->path);
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
        return $this->uri_pieces;
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
     * @return string The locale string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set the module that was requested
     *
     * @param string $module
     */
    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    /**
     * Get the name of the module that was requested
     *
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * Set the controller that was requested
     *
     * @param string $controller
     */
    public function setController(string $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * Get the name of the controller that was requested
     *
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * Set the action that was requested
     *
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * Get the name of the action that was requested
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Reset the Module Controller and Action values to "index" This is useful when rerouting or doing an internal
     * redirect to ensure prior values are removed.
     */
    public function resetMCAValues()
    {
        $this->setModule('index');
        $this->setController('index');
        $this->setAction('index');
    }

    /**
     * Return the IP address of the remote client. This attempts to backtrace through any non-anonymizing proxies.
     * <p>
     * Warning! This value is easily spoofed. If logging IP addresses, it is highly recommended to also log the
     * RawRemoteIP unless a proxy server is being used for the site, such as a load balancer.
     *
     * //TODO: make backtracing something that is configurable / disable-able
     *
     * @return string The remote IP address
     */
    public function getRemoteIP(): string
    {
        return $this->remote_ip;
    }

    public function getRawRemoteIP(): string
    {
        return $this->raw_remote_ip;
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
     *
     * @return array The pieces of the array
     */
    protected function processURI(string $url): array
    {
        $url = trim($url, '/');
        $url_array = explode('/', $url);
        $this->extractLocale($url_array);
        $filename = array_pop($url_array);
        $this->extension = pathinfo($filename, PATHINFO_EXTENSION);
        $url_array[] = $this->filename = pathinfo($filename, PATHINFO_FILENAME);
        return $url_array;
    }

    /**
     * Remove the locale from the first element in the array if one is set.
     *
     * @param array $url_array
     */
    protected function extractLocale(array &$url_array): void
    {
        if (count($url_array) > 0 && preg_match('/^([a-z0-9]{2,3})-([a-z0-9]{4}-?)?([a-z0-9]{2,3})?$/i',
                $url_array[0])) {
            $this->setLocale(array_shift($url_array));
        }
        reset($url_array);
    }

    /**
     * Get the IP address of the user
     *
     * @return string The remote IP address. If the user passes through a proxy, this will attempt to return the origin
     * IP address.
     */
    private function determineRemoteIP(): string
    {
        if (null === $this->remote_ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                $ip = explode(',', $ip);
                $ip = trim($ip[0]);
            }
            $this->remote_ip = $ip;
        }
        return $this->remote_ip;
    }


}