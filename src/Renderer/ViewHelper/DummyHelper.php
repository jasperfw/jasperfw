<?php
namespace JasperFW\JasperCore\Renderer\ViewHelper;

/**
 * Class DummyHelper
 *
 * The DummyHelper class is used when a call is made to a helper that is not attached to the current renderer. This
 * allows the call to fail silently. For example, a JSON renderer doesn't need to use a ViewHelper to generate a
 * navigation bar, so there is no reason to load that into memory. However, calls to that ViewHelper should not generate
 * error messages. This way, it is not necessary to check which ViewHelpers exists at a given point in the execution.
 *
 * @package JasperFW\JasperCore\Renderer\ViewHelper
 */
class DummyHelper extends ViewHelper
{
    /**
     * Override toString to return an empty string.
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }
}