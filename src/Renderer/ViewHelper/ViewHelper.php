<?php

namespace JasperFW\JasperFW\Renderer\ViewHelper;

use JasperFW\JasperFW\Lifecycle\Response;

/**
 * Class ViewHelper
 *
 * A ViewHelper is used to inject dynamic code into a rendered view. Unlike ViewHelperCollection, a ViewHelper will
 * typically represent one or more values, and set how they are to be displayed.
 *
 * @package JasperFW\JasperFW\Renderer\ViewHelper
 */
abstract class ViewHelper implements ViewHelperInterface
{
    protected array $values = [];
    /** @var null|Response */
    protected ?Response $response = null;
    /** @var string|null The name of the parent viewHelperCollection to which this item belongs */
    protected ?string $parent;

    /**
     * ViewHelper constructor.
     *
     * @param string|null $parent The name of the parent view helper
     */
    public function __construct(string $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * The init function is called when a view helper is loaded. This function should look in the configuration to find
     * any initial settings for the view helper.
     *
     * @param string|null $configuration The name of the settings in the config array
     */
    public function init(?string $configuration = null): void
    {
        // Does nothing by default
    }

    /**
     * Set a value to be displayed as part of the view helper.
     *
     * @param string $name  The name of the value to set
     * @param mixed  $value The value to set
     */
    public function __set(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }

    /**
     * Retrieves a stored value. Intended to be called by the __toString method.
     *
     * @param string $name The name of the value to return
     *
     * @return null|mixed
     */
    public function __get(string $name)
    {
        if (isset($this->values[$name])) {
            return $this->values[$name];
        }
        return null;
    }

    /**
     * Register a reference to the view helper
     *
     * @param Response $response The response object
     */
    public function registerResponse(Response &$response): void
    {
        $this->response = $response;
    }

    /**
     * Return the name of the parent view helper to add this view helper to.
     *
     * @return null|string
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * Override this method to display the values.
     *
     * @return string
     */
    abstract public function __toString(): string;
}
