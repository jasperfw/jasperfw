<?php
namespace JasperFW\JasperCore\Utility;

/**
 * Class HTTPUtilities
 * 
 * This class provides some common utilities related to the HTTP protocol, such as figuring out filenames. These
 * functions are all static, stateless, and have no dependencies. They should work the same in any context.
 */
class HTTPUtilities
{
    /**
     * Parse the passed filename to get the name without the extension. If passed an array, uses the last element of the
     * array.
     *
     * @param string|array $name
     *
     * @return string
     */
    public static function getFilename($name)
    {
        if (is_array($name)) {
            $name = array_pop($name);
        }
        return static::splitFilename($name)[0];
    }

    /**
     * Parse the passed filename to get the extension only. If passed an array, uses the last element of the array.
     *
     * @param string|array $name
     *
     * @return string
     */
    public static function getFileExtension($name)
    {
        if (is_array($name)) {
            $name = array_pop($name);
        }
        $extension = static::splitFilename($name)[1];
        if (is_string($extension)) {
            $extension = strtolower($extension);
        }
        return $extension;
    }

    /**
     * Splits the passed filename into a name and extension and returns an array. The first element is the name, the
     * second is the extension.
     *
     * @param string|array $name
     *
     * @return string[] The first element is the name, the second element is the extension, or null if no extension
     */
    private static function splitFilename($name)
    {
        if (is_array($name)) {
            $name = array_pop($name);
        } else {
            $name = explode('/', $name);
            $name = array_pop($name);
        }
        if (strrpos($name, '.') !== false) {
            return explode('.', $name);
        } else {
            return array($name, null);
        }
    }
}