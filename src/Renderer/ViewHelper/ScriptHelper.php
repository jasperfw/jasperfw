<?php
/**
 * Created by IntelliJ IDEA.
 * User: inter
 * Date: 10/12/2018
 * Time: 12:06 AM
 */

namespace WigeDev\JasperCore\Renderer\ViewHelper;

use WigeDev\JasperCore\Core;

/**
 * The ScriptHelper is used to define scripts that should be contained in the header. Scripts can be passed in a
 * few ways, as a string containing the actual script and a string with the type, as a string with the location of the
 * script file, or as a string with the location of the file and an array of other arguments to put into the link.
 */
class ScriptHelper extends ViewHelperCollection
{
    /** @var array The scripts */
    protected $scripts;

    /**
     * Creates the array to hold the scripts
     * @param null|string $parent
     */
    public function __construct(?string $parent = null)
    {
        parent::__construct($parent);
        $this->scripts = array();
    }

    /**
     * Get the scripts that are defined in the configuration.
     * @param null|string $configuration
     */
    public function init(?string $configuration = 'scripts') : void
    {
        $config = Core::i()->config->getConfiguration($configuration);
        foreach ($config as $script) {
            if (isset($script['order']) && $script['order'] == 'prepend') {
                $this->prepend($script);
            } else {
                $this->append($script);
            }
        }
    }

    /**
     * Add the passed value to the end of the collection
     */
    public function append()
    {
        $script = $this->parseArgs(func_get_args());
        if ($script !== false && !in_array($script, $this->members)) array_push($this->members, $script);
    }

    /**
     * Add the passed value to the beginning of the collection
     */
    public function prepend()
    {
        $script = $this->parseArgs(func_get_args());
        if ($script !== false && !in_array($script, $this->members)) array_unshift($this->members, $script);
    }

    /**
     * Return the value of the current member of the collection. Styling or formatting of the element should be done
     * in this function.
     * @return mixed
     */
    public function current()
    {
        return $this->members[$this->pointer];
    }

    /**
     * Override this method to display all the values. Typically, this will be done by performing a foreach on the
     * object so that current is called to return properly formatted strings. For example, if the child were being used
     * to generate an HTML list, the toString method would output <li> and </li> tags around a foreach loop of $this.
     * @return string
     */
    public function __toString() : string
    {
        $lines = '';
        foreach ($this as $line) {
            $lines .= $line . "\n";
        }
        return $lines;
    }

    /**
     * Builds the tag based on the passed info.
     * @param array $args
     * @return string
     */
    private function parseArgs(array $args) : string
    {
        if (!is_array($args)) return false;
        $components = array();
        if (count($args) == 1 && is_string($args[0])) {
            $components['src'] = $args[0];
            return $this->makeLink($components);
        } elseif (count($args) == 1 && is_array($args)) {
            $components = $args[0];
            return $this->makeLink($components);
        } elseif (count($args) == 2 && is_string($args[0]) && is_array($args[1])) {
            $args[1]['src'] = $args[0];
            $components = $args[1];
            return $this->makeLink($components);
        } elseif (count($args) == 2 && is_string($args[0]) && is_string($args[1])) {
            return '<script type="'.$args[1].'">'."\n".$args[0]."\n</script>\n";
        }
        Core::i()->log->warning('Unable to interpret script. Not added.');
        return false;
    }

    /**
     * Creates the link to store in the collection.
     *
     * @param $args
     *
     * @return bool|string
     */
    private function makeLink($args)
    {
        if (!isset($args['src'])) {
            Core::i()->log->warning('Unable to add header script, no src attribute.');
            return false;
        }
        $link = '<script';
        foreach ($args as $key=>$value) {
            $link .= ' '.$key.'="'.$value.'"';
        }
        $link .= '></script>';
        return $link;
    }
}