<?php
namespace WigeDev\JasperCore\ServiceManager;

/**
 * Class ServiceManager
 *
 * Service Managers provide basic functionality and dependency injection within the framework.
 *
 * @package WigeDev\JasperCore\ServiceManager
 */
abstract class ServiceManager
{
    /**
     * The init function contains any required setup or initialization logic.
     */
    public function init() {}
}