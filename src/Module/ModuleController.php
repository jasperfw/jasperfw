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
    public static function canView()
    {
        return true;
    }
}