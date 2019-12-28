<?php
namespace WigeDev\JasperCore\Utility;

use Exception;
use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Lifecycle\Response;

/**
 * Class ModuleControllerLoader
 *
 * This class loads Module Controllers based on their permissions and the existance of required functions. If needed,
 * this class can be overridden with one that maps the classes differently or uses a different naming scheme, etc.
 */
class ModuleControllerLoader
{
    /** @var Response Reference to the response object for retrieving values */
    protected $response;

    /**
     * Load the requested module/controller/action
     * @param Response $response The response managing the process
     */
    public function load(Response $response) : void
    {
        $this->response = $response;
        $this->checkModule();
        if ($this->response->getStatusCode() === 200) {
            try {
                $this->loadModule();
            } catch (Exception $exception) {
                // If an exception is thrown in the code and not caught, switch over to the error handler
                $this->response->setStatusCode(500);
                $this->loadErrorModule();
            }
        } else {
            $this->loadErrorModule();
        }
    }

    /**
     * Test the module to make sure it exists and can be loaded/viewed.
     */
    protected function checkModule() : void
    {
        $namespaced_class = $this->getFullyQualifiedClass($this->response->getModule(), $this->response->getController());
        if (!class_exists($namespaced_class)) {
            $this->response->setStatusCode(404);
            Core::i()->log->warning('Unable to load controller ' . $namespaced_class);
            return;
        }
        if (!call_user_func($namespaced_class . '::can_view')) {
            $this->response->setStatusCode(403);
            Core::i()->log->error('User is not authorized to view ' . $namespaced_class);
            return;
        }
        if (method_exists($namespaced_class, $this->getActionMethodName($this->response->getAction()))) {
            $this->response->setStatusCode(200);
        } elseif (method_exists($namespaced_class, 'indexAction')) {
            Core::i()->log->notice('Requested action ' . $this->response->getAction() . ' not found in ' . $namespaced_class);
            $this->response->setAction('index');
            $this->response->setStatusCode(200);
        } else {
            Core::i()->log->error('Controller ' . $namespaced_class . ' does not have a public indexAction method defined.');
            $this->response->setStatusCode(500);
        }
    }

    /**
     * Load a module as requested and mapped by the router.
     */
    protected function loadModule() : void
    {
        $fqn = $this->getFullyQualifiedClass($this->response->getModule(), $this->response->getController());
        $the_module = new $fqn();
        call_user_func_array(array($the_module, $this->getActionMethodName($this->response->getAction())), [$this->response->getVariables()]);
    }

    /**
     * Load an error module based on the error code.
     */
    protected function loadErrorModule() : void
    {
        $fqn = $this->getFullyQualifiedClass('index', 'index');
        $the_module = new $fqn();
        $action = $this->getActionMethodName((string)$this->response->getStatusCode());
        if (method_exists($fqn, $action)) {
            call_user_func_array(array($the_module, $action), [$this->response->getVariables()]);
        } else {
            // There is no error handler for this error, show the index but don't pass variables
            call_user_func_array(array($the_module, $this->getActionMethodName('index')), []);
        }
    }

    /**
     * Get the fully qualified class name of the controller.
     * @param string $module_name
     * @param string $controller_class
     * @return string
     */
    protected function getFullyQualifiedClass(string $module_name, string $controller_class) : string
    {
        return 'Application\\Module\\' . $this->getModuleName($module_name) . '\\Controller\\' . $this->getControllerName($controller_class);
    }

    protected function getModuleName(string $module) : string
    {
        return ucfirst($module);
    }

    protected function getControllerName(string $controller) : string
    {
        return ucfirst($controller) . 'Controller';
    }

    protected function getActionMethodName(string $action) : string
    {
        return $action . 'Action';
    }
}