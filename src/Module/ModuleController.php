<?php
namespace WigeDev\JasperCore\Module;

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
}