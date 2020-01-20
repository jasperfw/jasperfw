<?php

namespace WigeDev\JasperCore\Renderer;

use Exception;
use WigeDev\JasperCore\Jasper;
use WigeDev\JasperCore\Lifecycle\Response;

use function WigeDev\JasperCore\J;

/**
 * Class Renderer
 *
 * Different renderers handle different types of requests, displaying output in a variety of ways. For example, the HTML
 * renderer will output the data as specified in a view. The JSON renderer will output the data in a JSON format.
 *
 * @package WigeDev\JasperCore\Renderer
 */
abstract class Renderer
{
    /**
     * Loads the view helpers and outputs the response in an appropriate format.
     * @param Response $response
     */
    public function render(Response $response): void
    {
//        foreach ($response->getViewHelpers() as $name => $view_helper) {
//            if (is_a($view_helper, ViewHelperInterface::class)) {
//                /** @var $view_helper ViewHelperInterface */
//                if (null !== $view_helper->getParent() && !is_null($response->getViewHelper($view_helper->getParent()))) {
//                    $parent = $response->getViewHelper($view_helper->getParent());
//                    if (is_a($parent, ViewHelperCollection::class)) {
//                        /** @var $parent ViewHelperCollection */
//                        $parent->append($name, $view_helper);
//                    }
//                }
//            }
//        }
    }

    /**
     * Create a URL pointing to a static resource. This function is intended to be used to generate internal URLs
     * pointing to assets such as stylesheets and scripts. The main feature of this function is that it will prepend
     * the "base" URL of the site/application to the URL, as well as the locale string if $addLocale is true.
     *
     * @param string $url       The static internal URL that may need to be modified
     * @param bool   $addLocale True if the local string should be added
     *
     * @return string The new URL
     */
    public function generateStaticURL(string $url, bool $addLocale = false): string
    {
        $url = ltrim($url, '/');
        // If a locale was specified, add that to the beginning of the url
        if (Jasper::i()->locale_set) {
            $url = $this->getLinkLocale(Jasper::i()->locale) . '/' . $url;
        }
        // If a base folder is set, add it.
        $base = Jasper::i()->config->getConfiguration('framework')['base'] ?? null;
        if ($base !== null) {
            $url = $base . '/' . $url;
        }
        return '/' . $url;
    }

    /**
     * Create a url based on a route. Passed variables will replace placeholders in the route. If the variable is not
     * part of the route, the variable will be appended as a query string.
     *
     * @param string $route_name The name of the route
     * @param array  $variables  An array of variables
     *
     * @return string
     */
    public function generateURL($route_name, $variables = []): string
    {
        // Make sure the route configuration has been loaded
        if (!isset($this->routes)) {
            $this->routes = Jasper::i()->config->getConfiguration('routes');
        }
        // Make sure the named route exists, otherwise return an empty string
        if (!isset($this->routes[$route_name])) {
            J()->log()->warning('The specified route, ' . $route_name . ', was not defined.');
            return '';
        }
        // Merge the default values and the passed variables into an array
        $variables = array_merge($this->routes[$route_name]['defaults'], $variables);
        $query_string = [];
        $url = str_replace('[', '', $this->routes[$route_name]['route']);
        $url = str_replace(']', '', $url);
        foreach ($variables as $key => $value) {
            if (false !== strpos($url, ':' . $key . ':')) {
                // The variable is in the url, replace it
                $url = str_replace(':' . $key . ':', $value, $url);
            } elseif ($key != 'controller' && $key != 'action' && $key != 'module') {
                // The variable is not in the url, add it to the query string as long as it is not the controller or action
                $query_string[] = urlencode($key) . '=' . urlencode($value);
            }
        }
        // If there is a trailing '/index' remove it. There can be up to three (module, controller, action)
        $url = preg_replace('|(/index){1,3}$|', '', $url);
        // Add the query string
        if (count($query_string) > 0) {
            $url .= '?' . implode('&', $query_string);
        }
        $url = ltrim($url, '/');
        // If a locale was specified, add that to the beginning of the url
        if (Jasper::i()->locale_set) {
            $url = $this->getLinkLocale(Jasper::i()->locale) . '/' . $url;
        }
        // If a base folder is set, add it.
        $base = Jasper::i()->config->getConfiguration('framework')['base'] ?? null;
        if ($base !== null) {
            $url = $base . '/' . $url;
        }
        return '/' . $url;
    }

    /**
     * Create an absolute path for use in links, to use in place of relative links that can be affected by locale
     * information.
     *
     * @param string $path
     * @param bool   $include_locale
     *
     * @return string
     * @throws Exception
     */
    public function createLink($path, $include_locale = false)
    {
        $protocol = (Jasper::i()->request->isSecure()) ? 'https:' : 'http:';
        $base = $this->getBaseURL($include_locale);
        return $protocol . $base . $path;
    }

    /**
     * Calculates and returns the base URL for the site. This is used in the base HREF tag, and can also be used for
     * generating internal links to resources that don't correspond to Framework 2 paths.
     *
     * @param bool $include_locale True to include the locale, if present, at the end of the URL
     *
     * @return string The calculated base URL
     *
     * @throws Exception
     */
    public function getBaseURL($include_locale = true)
    {
        $base = '//' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['URL']);
        if ($include_locale && Jasper::i()->locale_set) {
            $base .= $this->getLinkLocale(Jasper::i()->locale) . '/';
        }
        return $base;
    }

    private function getLinkLocale($locale)
    {
        if (false === strpos($locale, "-")) {
            return $locale . "-";
        }
        return $locale;
    }
}