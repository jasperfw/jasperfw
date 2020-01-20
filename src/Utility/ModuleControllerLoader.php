<?php
namespace WigeDev\JasperCore\Utility;

use Exception;
use WigeDev\JasperCore\Jasper;
use WigeDev\JasperCore\Lifecycle\Response;

use function WigeDev\JasperCore\J;

/**
 * Class ModuleControllerLoader
 *
 * This class loads Module Controllers based on their permissions and the existance of required functions. If needed,
 * this class can be overridden with one that maps the classes differently or uses a different naming scheme, etc.
 */
class ModuleControllerLoader
{
    /**
     * Load the requested module/controller/action
     * @param Response $response The response managing the process
     */
    public function load(Response $response): void
    {
        $this->checkModule($response);
        if ($response->getStatusCode() === 200) {
            try {
                $this->loadModule($response);
            } catch (Exception $exception) {
                // If an exception is thrown in the code and not caught, switch over to the error handler
                J()->log->warning('An Exception happened: ' . $exception->getMessage(), $exception->getTrace());
                $response->setStatusCode(500);
            }
        }
    }

    /**
     * If the status of the response is not 200, runs the error module.
     *
     * @param Response $response The response object
     */
    public function loadError(Response $response): void
    {
        if ($response->getStatusCode() !== 200) {
            $this->loadErrorModule($response);
        }
    }

    /**
     * Test the module to make sure it exists and can be loaded/viewed.
     *
     * @param Response $response
     */
    protected function checkModule(Response $response): void
    {
        $namespaced_class = $this->getFullyQualifiedClass(
            $response->getModule(),
            $response->getController()
        );
        if (!class_exists($namespaced_class)) {
            $response->setStatusCode(404);
            Jasper::i()->log->warning('Unable to load controller ' . $namespaced_class);
            return;
        }
        if (!call_user_func($namespaced_class . '::canView')) {
            var_dump($namespaced_class);
            $response->setStatusCode(403);
            Jasper::i()->log->error('User is not authorized to view ' . $namespaced_class);
            return;
        }
        if (method_exists($namespaced_class, $this->getActionMethodName($response->getAction()))) {
            return;
        } elseif (method_exists($namespaced_class, 'indexAction')) {
            Jasper::i()->log->notice(
                'Requested action ' . $response->getAction() . ' not found in ' . $namespaced_class
            );
            $response->setAction('index');
        } else {
            Jasper::i()->log->error(
                'Controller ' . $namespaced_class . ' does not have a public indexAction method defined.'
            );
            $response->setStatusCode(500);
        }
    }

    /**
     * Load a module as requested and mapped by the router.
     *
     * @param Response $response
     */
    protected function loadModule(Response $response): void
    {
        $fqn = $this->getFullyQualifiedClass($response->getModule(), $response->getController());
        $the_module = new $fqn();
        call_user_func_array(
            [$the_module, $this->getActionMethodName($response->getAction())],
            [$response->getVariables()]
        );
    }

    /**
     * Load an error module based on the error code.
     *
     * @param Response $response
     */
    public function loadErrorModule(Response $response): void
    {
        $fqn = $this->getFullyQualifiedClass('index', 'index');
        $the_module = new $fqn();
        $action = $this->getActionMethodName((string)$response->getStatusCode());
        if (method_exists($fqn, $action)) {
            call_user_func_array([$the_module, $action], [$response->getVariables()]);
        } elseif (method_exists($fqn, $this->getActionMethodName('index'))) {
            call_user_func_array([$the_module, $this->getActionMethodName('index')], [$response->getVariables()]);
        } else {
            // There is no error handler for this error, show the index but don't pass variables
            $fqn = $this->getFullyQualifiedClass('index', 'index');
            $the_module = new $fqn();
            $action = $this->getActionMethodName('index');
            call_user_func_array([$the_module, $action], []);
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