<?php
namespace WigeDev\JasperCore\Module;

use ReflectionClass;
use ReflectionException;

use function WigeDev\JasperCore\J;

/**
 * Class ModuleController
 *
 * Module controllers should extend this class.
 *
 * @package WigeDev\JasperCore\Module
 */
abstract class ModuleController
{
    /**
     * Checks the user permissions to determine if the current user can view this module. If the user can not view the
     * module, by default the user will be redirected to IndexModule > IndexController > indexAction.
     *
     * Generally, having IndexModule > IndexController return false would be a "bad thing" since that is usually where
     * the login form is served from.
     * @return bool
     */
    public static function canView(): bool
    {
        return true;
    }

    /**
     * ModuleController constructor.
     */
    public function __construct()
    {
        J()->response->setViewPath($this->getViewPath());
    }

    /**
     * Get the path to the view files for this class. This is done here to deal with case snesitive platforms.
     *
     * @return string The path to the view folder for this controller
     */
    protected function getViewPath(): string
    {
        try {
            $reflector = new ReflectionClass(static::class);
            $dirname = dirname($reflector->getFileName());
            return $dirname . DS . '..' . DS . 'View' . DS . J()->response->getController();
        } catch (ReflectionException $exception) {
            J()->log->warning('Unable to get view path for controller ' . static::class);
            return '';
        }
    }
}