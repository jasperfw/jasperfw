<?php

namespace WigeDev\JasperCore\Lifecycle;

use Exception;
use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Exception\NoRouteMatchException;

/**
 * Class Router
 *
 * Simply put, the router takes a requested URI and routes the request to the appropriate Module, Controller and action
 * following the routing rules that are set in the framework's configuration file.
 *
 * @package WigeDev\JasperCore\Lifecycle
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
     * Do the routing
     *
     * @param Request  $request  The request container
     * @param Response $response The object managing the response to the request
     *
     * @throws Exception
     */
    public function route(Request $request, Response $response): void
    {
        $this->reroutes++;
        if ($this->reroutes > 10) {
            Core::i()->log->critical('The request for ' . $request->getURI() . ' has redirected too many times!');
            $response->setStatusCode(508);
            return;
        }
        // Get the module and action if provided.
        try {
            $variables = $this->matchRoute($request->getUriPieces());
        } catch (NoRouteMatchException $e) {
            Core::i()->log->critical('The requested URL ' . $request->getURI() . ' could not be found.');
            $response->setStatusCode(404);
            return;
        }
        $response->resetMCAValues();
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
        // Store the variables for retrieval TODO: Should this be done? Seems insecure.
//        if (count($variables) > 0) {
//            $response->setValues($variables);
//        }
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
        Core::i()->log->debug('URL matched route ' . $route_name);
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
        }
        return false;
    }

    /**
     * Load the routes from the configuration
     */
    protected function loadRoutes(): void
    {
        $routeConfig = Core::i()->config->getConfiguration('routes');
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
}