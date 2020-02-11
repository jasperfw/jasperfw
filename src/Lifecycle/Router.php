<?php

namespace JasperFW\JasperFW\Lifecycle;

use Exception;
use JasperFW\JasperFW\Exception\NoRouteMatchException;

use function JasperFW\JasperFW\J;

/**
 * Class Router
 *
 * Simply put, the router takes a requested URI and routes the request to the appropriate Module, Controller and action
 * following the routing rules that are set in the framework's configuration file.
 *
 * @package JasperFW\JasperFW\Lifecycle
 */
class Router
{
    /** @var array Routes defined in the configuration */
    protected $route_definitions;
    /** @var int The number of times the request has been internally rerouted or redirected. */
    private $reroutes;

    public function __construct()
    {
        $this->reroutes = 0;
    }

    /**
     * Do the routing. By default this uses path information set in the request object. However, if a new path is passed
     * the router will use that new path and override what is set in request. This will also update the request object.
     *
     * @param string|null $path An optional path to route to. Basically an internal redirect.
     *
     * @throws Exception
     */
    public function route(string $path = null): void
    {
        $request = J()->request;
        $response = J()->response;
        if ($path != null) {
            $request->setURI($path);
        }
        $this->reroutes++;
        if ($this->reroutes > 10) {
            J()->log->critical('The request for ' . $request->getURI() . ' has redirected too many times!');
            $response->setStatusCode(508);
            return;
        }
        // Clear the MCA values back to default
        $response->resetMCAValues();
        // Get the module and action if provided.
        try {
            $variables = $this->matchRoute($request->getUriPieces());
        } catch (NoRouteMatchException $e) {
            $response->setStatusCode(404);
            J()->log->warning('The requested URL ' . $request->getURI() . ' could not be found.');
            $response->addMessage('The requested URL ' . $request->getURI() . ' could not be found.');
            return;
        }
        if (isset($variables['module'])) {
            $response->setModule($variables['module']);
            unset($variables['module']);
        }
        if (isset($variables['controller'])) {
            $response->setController($variables['controller']);
            unset($variables['controller']);
        }
        if (isset($variables['action'])) {
            $response->setAction($variables['action']);
            unset($variables['action']);
        }
        $response->setViewType($this->determineViewType($request->getExtension()));
    }

    public function reRoute(string $route)
    {
    }

    /**
     * Take the url pieces and compare them to the routes set in the configuration file to extract variables.
     *
     * @param string[] $url_array An array of url pieces to be parsed to get the variables.
     *
     * @return array
     * @throws NoRouteMatchException
     */
    protected function matchRoute(array $url_array): array
    {
        // If the routes haven't already been set up, process them.
        if (!isset($this->route_definitions)) {
            $this->loadRoutes();
        }
        $url = '/' . implode('/', $url_array);
        $matches = $this->doRouteMatching($url);
        if (false === $matches) {
            throw new NoRouteMatchException('Unable to route url ' . $url);
        }
        $route_name = array_shift($matches);
        J()->log->debug('URL {0} matched route ' . $route_name, [implode('/', $url_array)]);
        $route_config = $this->route_definitions[$route_name];
        $return = (isset($route_config['defaults'])) ? $route_config['defaults'] : [];
        foreach ($matches as $name => $match) {
            if (!is_numeric($name)) { // If the key is a number, it is an artifact of the regex process, ignore it
                $return[$name] = $match;
            }
        }
        return $return;
    }

    /**
     * Processs the url.
     *
     * @param string $url The url to match
     *
     * @return array|bool
     */
    protected function doRouteMatching($url)
    {
        $matches = array();
        foreach ($this->route_definitions as $name => $route) {
            if (preg_match($route['regex'], $url, $matches)) {
                $matches[0] = $name;
                return $matches;
            }
            J()->log->debug('{0} did not match route {1}', [$url, $name, $route['regex']]);
        }
        return false;
    }

    /**
     * Load the routes from the configuration
     */
    protected function loadRoutes(): void
    {
        $routeConfig = J()->config->getConfiguration('routes');
        // If there is a default route, make it last in the array
        if (isset($routeConfig['default'])) {
            $default_route = $routeConfig['default'];
            unset($routeConfig['default']);
            $routeConfig['default'] = $default_route;
        }
        // Parse the routes and build the regex strings
        foreach ($routeConfig as $route_name => $route) {
            $regex = $route['route'];
            $regex = str_replace('/', '\/', $regex);
            $regex = str_replace('[', '(', $regex);
            $regex = str_replace(']', ')?', $regex);
            if (isset($route['constraints'])) {
                foreach ($route['constraints'] as $key => $constraint) {
                    $regex = str_replace(':' . $key . ':', "(?P<{$key}>" . $constraint . ')', $regex);
                }
            }
            $routeConfig[$route_name]['regex'] = '/^' . $regex . '$/i';
        }
        $this->route_definitions = $routeConfig;
    }

    /**
     * Determines the view type, using the request in the Framework.
     *
     * @param string $extension The extension of the request
     *
     * @return string The detected view type - either the extension or 'c l i'
     */
    protected function determineViewType(string $extension): string
    {
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
            // Extension is irrelevant, use the cli renderer
            return 'c l i';
        } elseif (false !== strpos($extension, ' ')) {
            // If the extension contains a space, go to the default type - spaces are for special cases only
            return '';
        } elseif (!is_null($extension)) {
            return $extension;
        }
        return '';
    }
}