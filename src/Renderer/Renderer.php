<?php

namespace JasperFW\JasperCore\Renderer;

use Exception;
use JasperFW\JasperCore\Lifecycle\Response;

use function JasperFW\JasperCore\J;

/**
 * Class Renderer
 *
 * Different renderers handle different types of requests, displaying output in a variety of ways. For example, the HTML
 * renderer will output the data as specified in a view. The JSON renderer will output the data in a JSON format.
 *
 * @package JasperFW\JasperCore\Renderer
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
        return J()->response->generateStaticURL($url, $addLocale);
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
        return J()->response->generateURL($route_name, $variables);
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
        return J()->response->createLink($path, $include_locale);
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
        return J()->response->getBaseURL($include_locale);
    }
}