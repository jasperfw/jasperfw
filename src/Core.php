<?php

namespace WigeDev\JasperCore;

// FUTURE: Enable the session handler
//require_once('SessionHandler.php');
//session_set_save_handler(new SessionHandler(), true);
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WigeDev\JasperCore\Event\EventHandler;
use WigeDev\JasperCore\Event\EventHandlerCollection;
use WigeDev\JasperCore\Exception\ServiceManagerNotFoundException;
use WigeDev\JasperCore\Lifecycle\Request;
use WigeDev\JasperCore\Lifecycle\Response;
use WigeDev\JasperCore\Lifecycle\Router;
use WigeDev\JasperCore\ServiceManager\ServiceManager;
use WigeDev\JasperCore\ServiceManager\ServiceManagerManager;
use WigeDev\JasperCore\Utility\Configuration;
use WigeDev\JasperCore\Utility\ModuleControllerLoader;

session_start();
date_default_timezone_set('UTC');
// If this is being run as a command line application, parse the first argument as a GET request string
if ('cli' == php_sapi_name()) {
    if (isset($argv)) {
        parse_str(implode('&', array_slice($argv, 1)), $_GET);
        $_REQUEST = $_GET;
    }
}
// Set up some shortcuts, makes sure others have been set
if (!defined('DS')) {
    /** Platform appropriate directory seperator, a shortcut for DIRECTORY_SEPARATOR */
    define('DS', DIRECTORY_SEPARATOR);
};
if (!defined('_SITE_PATH_') || !defined('_CONFIG_PATH_') || !defined('_ROOT_PATH_')) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('One or more paths were not defined.');
}
// This is here for debugging and unit testing reasons - SET IN index.php, NOT HERE!
if (!defined('_ROOT_PATH_')) {
    /** The path to the root of the installation. */
    define('_ROOT_PATH_', __DIR__);
}
if (!defined('_SITE_PATH_')) {
    /** The path to the site files (generally <root>/public) */
    define('_SITE_PATH_', __DIR__ . DS . 'public');
}
if (!defined('_CONFIG_PATH_')) {
    /** The path to the config folder or file (by default <root>/config/config.php) */
    define('_CONFIG_PATH_', __DIR__ . DS . 'config');
}
// Try to figure out the environment. If you are unit testing, the bootstrapper should set this to "test".
if (!defined('ENVIRONMENT')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        /** Environment - test, cli or production */
        define('ENVIRONMENT', 'production');
    } else {
        /** Environment - test, cli or production */
        define('ENVIRONMENT', 'cli');
    }
}
// If the environment is test or CLI, display errors
if (ENVIRONMENT === 'test' || ENVIRONMENT == 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('xdebug.var_display_max_depth', 5);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 1024);
}
// Register a custom error handler
set_error_handler(function ($errno, $errstr, $errfile = null, $errline = null) {
    if (false === Core::isInitialized()) {
        echo "<h1>Error 500</h1><p>Error $errno - $errstr in $errfile on $errline</p>";
    }
    // Process the error based on type
    switch ($errno) {
        case E_USER_ERROR:
        case E_ERROR:
            Core::i()->log->error("$errstr -  on line $errline in file $errfile");
            break;
        case E_USER_WARNING:
        case E_WARNING:
            Core::i()->log->warning("$errstr -  on line $errline in file $errfile");
            break;
        case E_RECOVERABLE_ERROR:
        case E_USER_NOTICE:
        case E_NOTICE:
            Core::i()->log->notice("$errstr -  on line $errline in file $errfile");
            break;
        default:
            echo "Unknown error type: [$errno] $errstr -  on line $errline in file $errfile<br />\n";
            break;
    }
    return true; // Prevent the built in error handler from being called
});

/**
 * Class Core
 *
 * The entry point for the framework, Core sets up the main functionality.
 *
 * @package WigeDev\JasperCore
 *
 * @property string                 locale       The ISO locale string, typically set in the URI
 * @property int                    $http_status The status code for the http request.
 * @property LoggerInterface        log          A reference to the log
 * @property Configuration          config       The configuration manager for the framework
 * @property Request                request      The container of information about the request
 * @property Response               response     The object managing the response to the client
 * @property string                 request_uri  The requested URI
 * @property Router                 router       The router class that handles the routing of incoming requests
 * @property ModuleControllerLoader mcl          The loader for module controllers
 */
class Core
{
    /** @var Core The framework object - I know, singleton, evil, bad. */
    private static $framework = null;
    /** @var Response The response object stores information about the response, from routing to views */
    protected $response;
    /** @var Request Container for information about the request */
    protected $request;
    /** @var ContainerInterface The dependency injection container */
    private $container;
    /** @var EventHandlerCollection */
    private $event_handlers;
    /** @var ServiceManagerManager The service managers that run critical services */
    private $service_managers;
    /** @var Configuration The configuration manager */
    private $config;
    private $output_type;

    /**
     * Function accesses the Core framework object. Initializes the framwork if it has not been done already.
     *
     * @return Core Reference to the framework singleton
     */
    public static function i(): Core
    {
        return static::$framework;
    }

    /**
     * Function checks if the framework has been initialized.
     */
    public static function isInitialized(): bool
    {
        return isset(static::$framework);
    }

    /**
     * Function initializes the framework object.
     *
     * @return Core
     */
    public static function _init(): Core
    {
        static::$framework = new Core();
        static::$framework->createDIContainer();
        try {
            static::$framework->fireEvent('initialized');
        } catch (Exception $e) {
            echo('Unable to fire initialization.');
            exit();
        }
        static::$framework->router = new Router();
        static::$framework->mcl = new ModuleControllerLoader();
        return static::$framework;
    }

    /**
     * Core constructor. Public access to enable unit testing.
     */
    public function __construct()
    {
        $this->event_handlers = new EventHandlerCollection();
        $this->service_managers = new ServiceManagerManager();
        $this->config = new Configuration(_CONFIG_PATH_);
        $this->request = new Request();
        $this->response = new Response();
        //TODO: Get the log settings from config and set up logging.
    }

    public function run(): void
    {
        try {
            $this->fireEvent('parsingrequest');
            $this->fireEvent('requestparsed');
            $this->fireEvent('beforeroute');
            $this->router->route($this->request, $this->response);
            $this->fireEvent('afterroute');
            $this->fireEvent('beforeload');
            $this->mcl->load($this->response);
            $this->fireEvent('afterload');
            $this->fireEvent('beforerender');
            $this->response->render(); //TODO: Uncomment this
            $this->fireEvent('afterrender');
            $this->fireEvent('beginshutdown');
        } catch (Exception $exception) {
            // If an uncaught exception gets here, there isn't much we can really do but log it and show a basic error
            //FUTURE: Make this more robust and maybe even customizeable
            echo 'Error 500 - An unexpected error has occurred.';
            $this->log->critical('An uncaught exception occurred. ' . $exception->getMessage());
        }
    }

    /**
     * Set values in the framework.
     *
     * @param string $name  The name of the value being set
     * @param mixed  $value The value to set
     */
    public function __set(string $name, $value): void
    {
        switch ($name) {
            case 'locale':
                $this->request->setLocale($value);
                break;
        }
    }

    /**
     * Get values or service managers from the framework.
     *
     * @param string $name The name of the value or service manager to return
     *
     * @return mixed The requested value, or false if it could not be found.
     * @throws ServiceManagerNotFoundException
     */
    public function __get(string $name)
    {
        switch ($name) {
            case 'config':
                return $this->config;
            case 'output_type':
                return $this->output_type;
            case 'c':
                return $this->container;
            case 'log':
                if ($this->service_managers->exists('log')) {
                    return $this->service_managers->get('log');
                } else {
                    return new NullLogger();
                }
            case 'request':
                return $this->request;
            case 'response':
                return $this->response;
            case 'router':
                return $this->router;
            default:
                if ($this->service_managers->exists($name)) {
                    return $this->service_managers->get($name);
                }
        }
        return false;
    }

    /**
     * Register a service manager.
     *
     * @param string         $name The name of the service manager for use in retrieving a reference to it
     * @param ServiceManager $service_manager
     *
     * @return Core
     */
    public function registerServiceManager(string $name, ServiceManager $service_manager): Core
    {
        $this->service_managers->register($name, $service_manager);
        return $this;
    }

    /**
     * Register an event listener to be called when a specific event is fired.
     *
     * @param EventHandler $listener
     *
     * @return Core
     * @throws Exception
     *
     */
    public function registerEventHandler(EventHandler $listener): Core
    {
        $this->event_handlers->addItem($listener);
        return $this;
    }

    /**
     * Create an event handler to be called when a specified event is fired by the framework or a library within the
     * Framework.
     *
     * @param string $event        The name of the event to listen for
     * @param mixed  $class_or_obj The object or class the method belongs to
     * @param string $method       The method to call
     * @param array  $arguments    Optional parameters to pass to the method
     *
     * @return Core
     * @throws Exception
     */
    public function on(string $event, $class_or_obj, string $method, array $arguments = []): Core
    {
        $this->registerEventHandler(new EventHandler($event, $class_or_obj, $method, $arguments));
        return $this;
    }

    /**
     * Register a callback to be run before the renderer is called
     *
     * @param string $event The name of the event being fired
     *
     * @throws Exception
     */
    public function fireEvent(string $event): void
    {
        $this->log->debug('Event {event} has been fired.', ['event' => $event]);
        foreach ($this->event_handlers as $handler) {
            /** @var EventHandler $handler */
            if ($event === $handler->getEvent()) {
                $handler->execute();
            }
        }
    }

    /**
     * @return string 'production' or 'development'
     */
    public function getEnvironment(): string
    {
        return ENVIRONMENT;
    }

    /**
     * Set up the dependency injection container for the application
     */
    protected function createDIContainer()
    {
        //$builder = new \DI\ContainerBuilder();
        //// Enable caching for production environment - this extension is deprecated.
        ////if ('production' === ENVIRONMENT) {
        ////    $builder->setDefinitionCache(new Doctrine\Common\Cache\ApcCache());
        ////}
        //if (!empty($this->config->parseConfigurationKeepLast('dependencies'))) {
        //    $builder->useAnnotations(true);
        //    $builder->addDefinitions($this->config->parseConfigurationKeepLast('dependencies'));
        //}
        //$this->container = $builder->build();
    }
}

/**
 * Shortcut to access the framework
 */
function FW()
{
    try {
        return Core::i();
    } catch (Exception $exception) {
        Core::i()->log->critical('An uncaught exception has occurred: ' . $exception->getMessage());
        exit();
    }
}