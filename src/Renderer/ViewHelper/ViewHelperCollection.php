<?php
namespace WigeDev\JasperCore\Renderer\ViewHelper;

use WigeDev\JasperCore\Renderer\Renderer;
use WigeDev\JasperFarm\Utility\Collection;

/**
 * Class ViewHelperCollection
 *
 * A view helper collection is used to display several elements. The most common use is in head elements to store meta
 * tags and scripts. It can also be used to store data for display in a list, for example.
 * The class implements Iterator so its children can be processed in a foreach as if this was an array. Override the
 * "next"
 *
 * @package WigeDev\JasperCore\Renderer\ViewHelper
 */
abstract class ViewHelperCollection extends Collection implements ViewHelperInterface
{
    /** @var ViewHelper[] The items in the collection */
    protected $members;
    /** @var int The index of the current element */
    protected $pointer;
    /** @var Renderer|null A reference to the current renderer */
    protected $renderer;
    /** @var string|null The name of the parent viewhelper collection */
    protected $parent;

    public function __construct(?string $parent = null)
    {
        parent::__construct();
        $this->parent = $parent;
    }

    public function init(?string $configuration = null) : void
    {
        // doesn't do anything by default
    }

    /**
     * Add the passed value to the end of the collection
     */
    public function append()
    {
        array_push($this->members, func_get_args());
    }

    /**
     * Add the passed value to the beginning of the collection
     */
    public function prepend()
    {
        array_unshift($this->members, func_get_args());
    }

    /**
     * Reset the pointer to the beginning of the collection
     */
    public function rewind() : void
    {
        $this->pointer = 0;
    }

    /**
     * Check if there are more members of the collection
     * @return bool True if there are more elements in the collection
     */
    public function hasMore() : bool
    {
        return $this->pointer < count($this->members);
    }

    /**
     * Return the key of the current member of the collection
     * @return string The key of the current member of the collection
     */
    public function key() : string
    {
        return array_keys($this->members)[$this->pointer];
    }

    /**
     * Return the value of the current element of the collection. This method should apply any needed styling of the
     * element to make it fit in with the collection.
     */
    abstract public function current();

    /**
     * Asvance to the next member of the collection
     */
    public function next() : void
    {
        $this->pointer ++;
    }

    /**
     * Reverse the pointer to the previous member of the collection.
     */
    public function previous() : void
    {
        $this->pointer--;
    }

    /**
     * Check if the current position of the pointer is valid
     *
     * @return bool True if the pointer position is valid
     */
    public function valid() : bool
    {
        if ($this->pointer >= 0 && $this->pointer < count($this->members)) {
            return true;
        }
        return false;
    }

    /**
     * Register a reference to the renderer
     *
     * @param Renderer $renderer The renderer that will be displaying this ViewHelperCollection
     */
    public function registerRenderer(Renderer $renderer) : void
    {
        $this->renderer = $renderer;
    }

    /**
     * Override this method to define how the collection and its members should be rendered. Typically this is done using
     * a foreach to iterate over the members and call the various __toString methods.
     * @return string The output of this ViewHelper and its members
     */
    abstract public function __toString() : string;

    /**
     * Return the name of the parent element of this collection, or null if no parent is defined.
     * @return null|string
     */
    public function getParent() : ?string
    {
        return $this->parent;
    }
}