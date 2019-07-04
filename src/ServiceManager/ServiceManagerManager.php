<?php
namespace WigeDev\JasperCore\ServiceManager;

use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Exception\ServiceManagerNotFoundException;

/**
 * Class ServiceManagerManager
 *
 * The ServiceManagerManager is not a service manager itself. Rather it is a collection that contains and manages the
 * service managers that are enabled in the framework. This provides the framework with a simplified form of dependency
 * injection.
 *
 * @package WigeDev\JasperCore\ServiceManager
 */
class ServiceManagerManager
{
    /** @var ServiceManager[] */
    protected $service_managers;

    /**
     * Add a service manager
     * @param string $name The name of the service manager
     * @param ServiceManager $service_manager The service manager being added
     */
    public function register(string $name, ServiceManager $service_manager)
    {
        $this->service_managers[$name] = $service_manager;
        if (Core::i()->log) {
            Core::i()->log->debug('Registered service manager: ' . $name);
        }
    }

    /**
     * Returns the specified service manager
     *
     * @param $name
     *
     * @return ServiceManager
     * @throws ServiceManagerNotFoundException
     */
    public function get($name)
    {
        if (isset($this->service_managers[$name])) {
            return $this->service_managers[$name];
        } else {
            throw new ServiceManagerNotFoundException('No service manager has been registered as ' . $name);
        }
    }

    /**
     * Checks if the service manager exists
     *
     * @param string $name The name of the service manager to look for
     *
     * @return bool True if the service manager exists
     */
    public function exists($name)
    {
        return isset($this->service_managers[$name]);
    }
}