<?php
namespace WigeDev\JasperCore\Lifecycle;

use Exception;
use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Exception\RenderingException;
use WigeDev\JasperCore\Renderer\Renderer;
use WigeDev\JasperCore\Renderer\ViewHelper\ViewHelper;
use WigeDev\JasperCore\Utility\HTTPUtilities;

/**
 * Class Response
 *
 * This class represents the response that will be returned to the client. Everything from the view and rendering to the
 * status code and error messages are managed through this class.
 *
 * TODO: Set the layout and view paths, add getters
 */
class Response
{
    /** @var int The HTTP status code */
    protected $status_code = 200;
    /** @var Renderer The renderer that will be managing the output */
    protected $renderer;
    /** @var bool True if the request has been routed. This does not indicate the success or failure of the routing. */
    protected $is_routed = false;
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
    /** @var string The path to the view file */
    protected $view_path;
    /** @var string The filename of the view file */
    protected $view_file;
    /** @var ViewHelper[] The view helpers */
    protected $view_helpers;
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
    }

    /**
     * Set a value to be included in the rendered view
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value): void
    {
        $this->values[$name] = $value;
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
    public function getRenderer(): Renderer
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
     * Set a value to be used in the rendered output. This is typically a single value that will be put into an HTML
     * view or a web page.
     * @param string $key The name of the value
     * @param mixed $value The value, may be a string or other object
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
        $config = Core::i()->config->getConfiguration('view');
        //$config = $this->parseConfiguration($config);
        foreach ($config as $key => $configuration) {
            if ($key === 'renderers') {
                $this->renderers = $configuration;
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

    /**
     * Custom configuration parser that will integrate certain elements of the configuration as returned by the
     * configuration manager.
     *
     * @param array[] $configuration
     *
     * @return array The parsed configuration
     */
    protected function parseConfiguration($configuration)
    {
        $return = [];
        foreach ($configuration as $page) {
            foreach ($page as $key => $setting) {
                if ($key == 'helpers') {
                    foreach ($setting as $a => $b) {
                        if (!isset($return['renderers'])) {
                            $return['renderers'] = [];
                        }
                        if (!isset($return['renderers'][$a])) {
                            $return['renderers'][$a] = array();
                        }
                        if (!isset($return['renderers'][$a]['helpers'])) {
                            $return['renderers'][$a]['helpers'] = array();
                        }
                        $return['renderers'][$a]['helpers'] = array_replace($return['renderers'][$a]['helpers'], $b);
                    }
                } else {
                    $return[$key] = $setting;
                }
            }
        }
        return $return;
    }

    /**
     * Determines the view type, using the request in the Framework.
     */
    protected function determineViewType()
    {
        $filename = Core::i()->request_uri;
        $extension = HTTPUtilities::getFileExtension($filename);
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'cli-server') {
            // Extension is irrelevant, use the cli renderer
            $this->setViewType('c l i');
            $url_array[] = $filename;
        } elseif (!is_null($extension)) {
            $this->setViewType($extension);
        }
        Core::i()->log->debug('View Type: ' . $this->view_type);
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
            return '_default.twig';
        }
    }

    /**
     * Get the path to the view file. If no path has been set, uses the default path.
     * @return string The path to the view file
     */
    public function getViewPath(): string
    {
        if ($this->view_path === null) {
            return _ROOT_PATH_ . DS . 'src' . DS . 'Module' . DS . $this->getModule() . DS . 'View' . DS . $this->getController();
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
            return $this->action . '.twig';
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

}