<?php
namespace JasperFW\JasperCore\Lifecycle;

use Exception;
use JasperFW\JasperCore\Exception\RenderingException;
use JasperFW\JasperCore\Jasper;
use JasperFW\JasperCore\Renderer\Renderer;
use JasperFW\JasperCore\Renderer\ViewHelper\ViewHelper;

use function JasperFW\JasperCore\J;

/**
 * Class Response
 *
 * This class represents the response that will be returned to the client. Everything from the view and rendering to the
 * status code and error messages are managed through this class.
 */
class Response
{
    /** @var int The HTTP status code */
    protected $status_code = 200;
    /** @var Renderer The renderer that will be managing the output */
    protected $renderer;
    /** @var array The variables passed as part of the request */
    protected $variables = [];
    /** @var string[] Error messages and other output strings */
    protected $messages = [];
    /** @var array The values that may be embedded into the rendered view returned to the client */
    protected $values = [];
    /** @var mixed The data payload of the response - typically an array */
    protected $data;
    /** @var string The default renderer type */
    protected $default_view_type;
    /** @var string The renderer type */
    protected $view_type;
    /** @var string the Module the router has routed to */
    protected $module;
    /** @var string The Controller the router has routed to */
    protected $controller;
    /** @var string The Action the router has routed to */
    protected $action;
    /** @var string The path to the layout file */
    protected $layout_path;
    /** @var string The name of the layout file */
    protected $layout_file;
    /** @var string The path to the view file */
    protected $view_path;
    /** @var string The filename of the view file */
    protected $view_file;
    /** @var ViewHelper[] The view helpers */
    //protected $view_helpers;
    /** @var array List of renderers and their settings */
    protected $renderers;
    /** @var array Mapping of file extensions to renderer names. * is default */
    protected $extension_map;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->setLayoutPath(_ROOT_PATH_ . DS . 'layout'); // default layout folder, can be overrideen in the config
        $this->loadConfiguration();
        $this->resetMCAValues();
    }

    /**
     * Set a value to be included in the rendered view
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value): void
    {
        $this->setValue($name, $value);
    }

    /**
     * Get a value that has been set to be included in the rendered view
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->values[$name])) {
            return $this->values[$name];
        } else {
            return null;
        }
    }

    /**
     * Set a value to be used in the rendered output. This is typically a single value that will be put into an HTML
     * view or a web page.
     *
     * @param string $key   The name of the value
     * @param mixed  $value The value, may be a string or other object
     */
    public function setValue(string $key, $value): void
    {
        $this->values[$key] = $value;
    }

    /**
     * Add multiple values at once. Any existing values with duplicate keys will be replaced with the new value.
     *
     * @param array $values The new values
     */
    public function setValues(array $values): void
    {
        $this->values = array_merge($this->values, $values);
    }

    /**
     * Get the values to include into the rendered output
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set the data payload of the response. Typically this is an array. Responses that are informational web pages may
     * not have any data set at all.
     *
     * @param mixed $data The core data of the response.
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Returns the core data payload of the response.
     * @return mixed The data for the response
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the variables that were submitted as the request
     *
     * @param array $getVars  The variables that were part of the get
     * @param array $postVars The variables that were part of the post
     */
    public function setVariables(array $getVars, array $postVars): void
    {
        $this->variables = array_merge($getVars, $postVars);
    }

    /**
     * Get the variables that were set as part of the request
     *
     * @return array The values that have been set to include in the response
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Set an error message
     *
     * @param string $message
     */
    public function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * Get the error messages
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Set the status code to return with the request
     *
     * @param int $status_code The HTTP status code to send with the request
     */
    public function setStatusCode(int $status_code): void
    {
        $this->status_code = $status_code;
    }

    /**
     * Get the HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status_code;
    }

//    public function registerViewHelper(string $name, ViewHelper $viewHelper)
//    {
//        $viewHelper->registerResponse($this);
//        $this->view_helpers[$name] = $viewHelper;
//    }

//    public function getViewHelpers(): array
//    {
//        return $this->view_helpers;
//    }

//    public function getViewHelper(string $name): ?ViewHelper
//    {
//        if (isset($this->view_helpers[$name])) {
//            return $this->view_helpers[$name];
//        }
//        return null;
//    }

    /**
     * Reset the Module Controller and Action values to "index" This is useful when rerouting or doing an internal
     * redirect to ensure prior values are removed.
     */
    public function resetMCAValues()
    {
        $this->setModule('index');
        $this->setController('index');
        $this->setAction('index');
        $this->setViewType('');
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
     * Set the view type. The renderer that is used to display output will be determined based on this value. This is
     * typically set by the router based on the file extension, then the module or controller can update it based on
     * allowed types.
     *
     * @param string $viewType The view type
     */
    public function setViewType(string $viewType): void
    {
        $this->view_type = $viewType;
    }

    /**
     * Gets the view type. The renderer is based on the final view type set when render() is called.
     * @return string The view type
     */
    public function getViewType(): string
    {
        return $this->view_type;
    }

    /**
     * Get the renderer based on the request file extension
     * @return Renderer
     * @throws RenderingException
     */
    protected function getRenderer(): Renderer
    {
        if (isset($this->extension_map[$this->view_type])) {
            $renderClass = $this->renderers[$this->extension_map[$this->view_type]]['handler'];
        } elseif (isset($this->extension_map['*'])) {
            $renderClass = $this->renderers[$this->extension_map['*']]['handler'];
        } elseif (isset($this->extension_map[$this->default_view_type])) {
            $renderClass = $this->renderers[$this->extension_map[$this->default_view_type]]['handler'];
        } else {
            throw new RenderingException('No renderer found for ' . $this->view_type . ' files');
        }
        try {
            return new $renderClass();
        } catch (Exception $e) {
            throw new RenderingException('Unable to instantiate Renderer ' . $renderClass);
        }
    }

    /**
     * Perform the rendering operation.
     * @throws RenderingException
     */
    public function render()
    {
        $renderer = $this->getRenderer();
        $renderer->render($this);
    }

    protected function loadConfiguration()
    {
        $config = Jasper::i()->config->getConfiguration('view');
        //$config = $this->parseConfiguration($config);
        foreach ($config as $key => $configuration) {
            if ($key === 'renderers') {
                $this->renderers = $configuration;
            } elseif ($key === 'default_view_type') {
                $this->default_view_type = $configuration;
            } elseif ($key === 'default_layout_path') {
                $this->layout_path = $configuration;
            } elseif ($key === 'default_layout_file') {
                $this->layout_file = $configuration;
            } else {
                $this->__set($key, $configuration);
            }
        }
        $this->generateExtensionMap();
    }

    /**
     * Iterate through the extensions for each renderer and build a list of extensions and their related renderer.
     */
    protected function generateExtensionMap(): void
    {
        $this->extension_map = [];
        foreach ($this->renderers as $name => $renderer) {
            foreach ($renderer['extensions'] as $extension) {
                $this->extension_map[$extension] = $name;
            }
        }
    }

    public function getLayoutPath(): string
    {
        return $this->layout_path;
    }

    public function getLayoutFile(): string
    {
        if (isset($this->layout_file)) {
            return $this->layout_file;
        } else {
            return '_default';
        }
    }

    public function setLayoutFile(string $layout_file): void
    {
        $this->layout_file = $layout_file;
    }

    /**
     * Get the path to the view file. If no path has been set, uses the default path.
     * @return string The path to the view file
     */
    public function getViewPath(): string
    {
        if ($this->view_path === null) {
            return _ROOT_PATH_ . DS . 'src' . DS . 'Module' . DS . $this->getModule(
                ) . DS . 'View' . DS . $this->getController();
        }
        return $this->view_path;
    }

    /**
     * The name of the view file to be used in rendering
     * @param string $view_file The filename
     */
    public function setViewFile(string $view_file): void
    {
        $this->view_file = $view_file;
    }

    /**
     * Get the filename for the view. If none has been specified, returns the name of the action and '.twig'
     * @return string The filename
     */
    public function getViewFile(): string
    {
        if (null === $this->view_file) {
            return $this->action;
        }
        return $this->view_file;
    }

    public function setLayoutPath(string $newPath): void
    {
        $this->layout_path = $newPath;
    }

    public function setViewPath(string $newPath): void
    {
        $this->view_path = $newPath;
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
        if (J()->locale_set) {
            $url = $this->getLinkLocale(Jasper::i()->locale) . '/' . $url;
        }
        // If a base folder is set, add it.
        $base = J()->config->getConfiguration('framework')['base'] ?? null;
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
            $this->routes = J()->config->getConfiguration('routes');
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
            $url = $this->getLinkLocale(J()->locale) . '/' . $url;
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
        $protocol = (J()->request->isSecure()) ? 'https:' : 'http:';
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
        if ($include_locale && J()->locale_set) {
            $base .= $this->getLinkLocale(J()->locale) . '/';
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