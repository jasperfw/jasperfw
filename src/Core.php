<?php
namespace WigeDev\JasperCore;

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

/**
 * Class Core
 *
 * The entry point for the framework, Core sets up the main functionality.
 *
 * @package WigeDev\JasperCore
 *
 * @property string                 locale       The ISO locale string, typically set in the URI
 * @property int                    http_status  The status code for the http request.
 * @property LoggerInterface        log          A reference to the log
 * @property Configuration          config       The configuration manager for the framework
 * @property Request                request      The container of information about the request
 * @property Response               response     The object managing the response to the client
 * @property string                 request_uri  The requested URI
 * @property Router                 router       The router class that handles the routing of incoming requests
 * @property ModuleControllerLoader mcl          The loader for module controllers
 * @property ContainerInterface     c            The dependency injection container
 */
class Core
{
    /** @var Core The framework object - I know, singleton, evil, bad. */
    private static $framework = null;
    /** @var Response The response object stores information about the response, from routing to views */
    protected $response;
    /** @var Request Container for information about the request */
    protected $request;
    /** @var Router The router that determines the module controller and action to call */
    protected $router;
    /** @var ModuleControllerLoader The loader for module controllers */
    protected $mcl;
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
     * Allows for a replacement framework instance to be inserted. This is useful for unit testing.
     *
     * @param Core $core New framework instance
     */
    public static function overrideFramework(Core $core): void
    {
        static::$framework = $core;
    }

    /**
     * Function initializes the framework object.
     *
     * @return Core
     */
    public static function _init(): Core
    {
        // If the Framework has already been initialized, return it
        if (static::$framework !== null) {
            return static::$framework;
        }
        // Use the bootstrapper to set up the environment
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php');
        // Initialize the framework
        static::$framework = new Core();
        // Start the event handler
        try {
            static::$framework->fireEvent('initialized');
        } catch (Exception $e) {
            echo('Unable to fire initialization.');
            exit();
        }
        // Initialize the Request and Response objects
        static::$framework->request = new Request();
        static::$framework->response = new Response();
        // Return the framework
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
        $this->router = new Router();
        $this->mcl = new ModuleControllerLoader();
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
            var_dump($exception);
            echo 'Error 500 - An unexpected error has occurred.';
            var_dump($exception);
            $this->log->critical('An uncaught exception occurred. ' . $exception->getMessage());
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
    public function setDIContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Set a logger to record system messages during execution
     *
     * @param LoggerInterface $logger The log object that messages will be sent to
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->log = $logger;
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