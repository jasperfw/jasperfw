<?php
namespace WigeDev\JasperCore\Lifecycle;

use WigeDev\JasperCore\Core;
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
    protected $variables = array();
    /** @var string[] Error messages and other output strings */
    protected $messages = array();
    /** @var array The values that may be embedded into the rendered view returned to the client */
    protected $values = array();
    /** @var string The default renderer type */
    protected $default_view_type;
    /** @var string The renderer type */
    protected $view_type;
    /** @var string The path to the layout file */
    protected $layout_path;
    /** @var string The path to the view file */
    protected $view_path;
    /** @var ViewHelper[] The view helpers */
    protected $view_helpers;
    protected $renderers;

    /**
     * Response constructor.
     */
    public function __construct()
    {
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

    public function registerViewHelper(string $name, ViewHelper $viewHelper)
    {
        $viewHelper->registerResponse($this);
        $this->view_helpers[$name] = $viewHelper;
    }

    public function getViewHelpers(): array
    {
        return $this->view_helpers;
    }

    public function getViewHelper(string $name): ?ViewHelper
    {
        if (isset($this->view_helpers[$name])) {
            return $this->view_helpers[$name];
        }
        return null;
    }

    /**
     * Set the variables that were submitted as part of the request
     *
     * @param array $variables The variables passed as part of the request
     */
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    /**
     * Get the variables that were set as part of the request
     *
     * @return string[] The variables that were set as part of the request
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

    public function render()
    {
        $this->renderer->render($this);
    }

    private function loadConfiguration()
    {
        $config = Core::i()->config->getConfiguration('view');
        $config = $this->parseConfiguration($config);
        foreach ($config as $key => $configuration) {
            if ($key === 'renderers') {
                $this->renderers = $configuration;
            } else {
                $this->__set($key, $configuration);
            }
        }
        $this->determineViewType();
    }

    /**
     * Custom configuration parser that will integrate certain elements of the configuration as returned by the
     * configuration manager.
     *
     * @param array[] $configuration
     *
     * @return array The parsed configuration
     */
    private function parseConfiguration($configuration)
    {
        $return = [];
        foreach ($configuration as $page) {
            foreach ($page as $key => $setting) {
                if ($key == 'helpers') {
                    foreach ($setting as $a => $b) {
                        if (!isset($return['renderers'])) {
                            $return['renderers'] = array();
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

    /**
     * Determines based on the passed extension the proper file type
     *
     * @param $extension
     */
    protected function setViewType($extension)
    {
        foreach ($this->renderers as $type => $config) {
            if (in_array($extension, $config['extensions'])) {
                $this->view_type = $type;
            }
        }
        if (!isset($this->view_type)) {
            $this->view_type = $this->default_view_type;
        }
    }
}