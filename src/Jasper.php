<?php
namespace JasperFW\JasperFW;

use Exception;
use JasperFW\JasperFW\Event\EventHandler;
use JasperFW\JasperFW\Event\EventHandlerCollection;
use JasperFW\JasperFW\Lifecycle\Request;
use JasperFW\JasperFW\Lifecycle\Response;
use JasperFW\JasperFW\Lifecycle\Router;
use JasperFW\JasperFW\Utility\Configuration;
use JasperFW\JasperFW\Utility\ModuleControllerLoader;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Jasper
 *
 * The entry point for the framework, Jasper sets up the main functionality.
 *
 * @package JasperFW\JasperFW
 *
 * @property int                    httpStatus   The status code for the http request.
 * @property LoggerInterface        log          A reference to the log
 * @property Configuration          config       The configuration manager for the framework
 * @property Request                request      The container of information about the request
 * @property Response               response     The object managing the response to the client
 * @property Router                 router       The router class that handles the routing of incoming requests
 * @property ModuleControllerLoader mcl          The loader for module controllers
 * @property ContainerInterface     c            The dependency injection container
 */
class Jasper
{
    /** @var Jasper|null The framework object - I know, singleton, evil, bad. */
    private static ?Jasper $framework = null;
    /** @var array Configuration file and folder paths */
    protected static array $configurations = [];
    /** @var ContainerInterface|null The dependency injection container */
    protected static ?ContainerInterface $container = null;
    /** @var LoggerInterface Reference to the log utility */
    protected static LoggerInterface $logger;
    /** @var Response The response object stores information about the response, from routing to views */
    protected Response $response;
    /** @var Request Container for information about the request */
    protected Request $request;
    /** @var Router The router that determines the module controller and action to call */
    protected Router $router;
    /** @var ModuleControllerLoader The loader for module controllers */
    protected ModuleControllerLoader $mcl;
    /** @var EventHandlerCollection The event handlers */
    protected EventHandlerCollection $eventHandlers;
    /** @var Configuration The configuration manager */
    protected Configuration $config;

    /**
     * Function accesses the Core framework object. Initializes the framwork if it has not been done already.
     *
     * @return Jasper Reference to the framework singleton
     */
    public static function i(): Jasper
    {
        return static::$framework;
    }

    /**
     * Specify a path to a configuration file or a directory of configuration files that should be processed. If the
     * framework is already initialized, processes the configuration file.
     *
     * @param string $configurationPath
     */
    public static function addConfigurationPath(string $configurationPath): void
    {
        static::$configurations[] = $configurationPath;
        if (static::isInitialized()) {
            if (is_file($configurationPath)) {
                static::i()->config->parseFile($configurationPath);
            } else {
                static::i()->config->parseFolder($configurationPath);
            }
        }
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
     * @param Jasper $core New framework instance
     */
    public static function overrideFramework(Jasper $core): void
    {
        static::$framework = $core;
    }

    /**
     * Set up the dependency injection container for the application
     *
     * @param ContainerInterface $container
     */
    public static function setDIContainer(ContainerInterface $container): void
    {
        static::$container = $container;
    }

    /**
     * Set a logger to record system messages during execution
     *
     * @param LoggerInterface $logger The log object that messages will be sent to
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        static::$logger = $logger;
    }

    /**
     * Set up the environment variables
     */
    public static function bootstrap(): void
    {
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php');
    }

    /**
     * Function initializes the framework object.
     *
     * @return Jasper
     */
    public static function _init(): Jasper
    {
        // If the Framework has already been initialized, return it
        if (static::$framework !== null) {
            return static::$framework;
        }
        // Use the bootstrapper to set up the environment
        static::bootstrap();
        // Initialize the framework
        static::$framework = new Jasper();
        // Start the event handler
        try {
            static::$framework->fireEvent('initialized');
        } catch (Exception $e) {
            var_dump($e);
            echo('Unable to fire initialization.');
            exit();
        }
        // Initialize the Request and Response objects
        static::$framework->fireEvent('parsingrequest');
        static::$framework->request = new Request();
        static::$framework->response = new Response();
        static::$framework->fireEvent('requestparsed');
        // Return the framework
        return static::$framework;
    }

    /**
     * Core constructor. Public access to enable unit testing.
     */
    public function __construct()
    {
        $this->eventHandlers = new EventHandlerCollection();
        $this->config = new Configuration(static::$configurations);
        $this->router = new Router();
        $this->mcl = new ModuleControllerLoader();
    }

    /**
     * Execute the request.
     */
    public function run(): void
    {
        // Try standard routing
        try {
            $this->fireEvent('beforeroute');
            $this->router->route();
            $this->fireEvent('afterroute');
            if ($this->response->getStatusCode() !== 200) {
                throw new Exception($this->response->getStatusCode());
            }
            $this->fireEvent('beforeload');
            $this->mcl->load();
            $this->fireEvent('afterload');
            if ($this->response->getStatusCode() !== 200) {
                throw new Exception($this->response->getStatusCode());
            }
        } catch (Exception $exception) {
            // Trigger the error handler
            try {
                $this->fireEvent('beforeerrorhandling');
                $this->router->route('/error/error' . $this->response->getStatusCode());
                $this->mcl->load(false);
                $this->fireEvent('aftererrorhandling');
            } catch (Exception $exception2) {
                // If an exception happens in the renderer itelf, we have to output a plain text error.
                echo 'Error 500 - An unexpected error has occurred in the renderer. ' . $exception->getMessage();
                $this->log->error('An error handling exception occurred. ' . $exception2->getMessage());
            }
        }
        // Try rendering the request
        try {
            $this->fireEvent('beforerender');
            $this->response->render();
            $this->fireEvent('afterrender');
        } catch (Exception $exception) {
            // If an exception happens in the renderer itelf, we have to output a plain text error.
            echo 'Error 500 - An unexpected error has occurred in the renderer. ' . $exception->getMessage();
            $this->log->error('An rendering exception occurred. ' . $exception->getMessage());
        }
        $this->fireEvent('beginshutdown');
    }

    /**
     * Get values or service managers from the framework.
     *
     * @param string $name The name of the value or service manager to return
     *
     * @return mixed The requested value, or false if it could not be found.
     */
    public function __get(string $name): mixed
    {
        switch ($name) {
            case 'config':
                return $this->config;
            case 'c':
                return static::$container;
            case 'log':
                if (!isset(static::$logger)) {
                    static::$logger = new NullLogger();
                }
                return static::$logger;
            case 'request':
                return $this->request;
            case 'response':
                return $this->response;
            case 'router':
                return $this->router;
            case 'mcl':
                return $this->mcl;
            default:
                return null;
        }
    }

    /**
     * Set values in the framework.
     *
     * @param string $name  The name of the value being set
     * @param mixed  $value The value to set
     */
    public function __set(string $name, mixed $value): void
    {
        switch ($name) {
            case 'locale':
                $this->request->setLocale($value);
                break;
        }
    }

    /**
     * Register an event listener to be called when a specific event is fired.
     *
     * @param EventHandler $listener
     *
     * @return Jasper
     *
     */
    public function registerEventHandler(EventHandler $listener): Jasper
    {
        $this->eventHandlers->addItem($listener);
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
     * @return Jasper
     */
    public function on(string $event, mixed $class_or_obj, string $method, array $arguments = []): Jasper
    {
        $this->registerEventHandler(new EventHandler($event, $class_or_obj, $method, $arguments));
        return $this;
    }

    /**
     * Register a callback to be run before the renderer is called
     *
     * @param string $event The name of the event being fired
     */
    public function fireEvent(string $event): void
    {
        $this->log->debug('Event "' . $event . '" has been fired.', ['event' => $event]);
        foreach ($this->eventHandlers as $handler) {
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
}

/**
 * Shortcut to access the framework
 */
function J()
{
    try {
        return Jasper::i();
    } catch (Exception $exception) {
        Jasper::i()->log->critical('An uncaught exception has occurred: ' . $exception->getMessage());
        exit();
    }
}
