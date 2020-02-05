<?php
/**
 * Created by IntelliJ IDEA.
 * User: inter
 * Date: 10/11/2018
 * Time: 11:56 PM
 */

namespace JasperFW\JasperFW\Renderer\ViewHelper;

use Exception;
use JasperFW\JasperFW\Jasper;

/**
 * Class StyleSheetHelper
 *
 * The stylesheet helper handles stylesheets for the document. Additionally, other link types can be created as well.
 * All require a href element (or a url as the first argument in the prepend or append call). If no rel is specified,
 * the link is assumed to be a stylesheet.
 */
class StylesheetHelper extends ViewHelperCollection
{
    /**
     * Get the stylesheets that are defined in the configuration.
     *
     * @param string|null $configuration The name of the settings array in the configuration
     *
     * @return array|void
     * @throws Exception
     */
    public function init(?string $configuration = 'stylesheets'): void
    {
        $config = Jasper::i()->config->getConfiguration($configuration);
        foreach ($config as $script) {
            $this->append($script);
        }
    }

    /**
     * Add a new stylesheet to the beginning of the list
     *
     * @param string ... The location of the stylesheet
     * @param string ... The type of stylesheet
     */
    public function prepend(): void
    {
        $script = $this->parseArgs(func_get_args());
        if ($script !== false) {
            array_unshift($this->members, $script);
        }
    }

    /**
     * Add a new stylesheet to the end of the list
     *
     * @param string ... The location of the stylesheet
     * @param string ... The type of stylesheet
     */
    public function append(): void
    {
        $script = $this->parseArgs(func_get_args());
        if ($script !== false) {
            array_push($this->members, $script);
        }
    }

    /**
     * Return the value of the current member of the collection. Styling or formatting of the element should be done
     * in this function.
     *
     * @return string
     */
    public function current(): string
    {
        return $this->members[$this->pointer];
    }

    /**
     * Loop through the stylesheet definitions and create a string to embed in the page header.
     *
     * @return mixed
     */
    public function __toString(): string
    {
        $return = '';
        foreach ($this as $line) {
            $return .= $line . "\n";
        }
        return $return;
    }

    /**
     * Builds the tag based on the passed info.
     *
     * @param $args
     *
     * @return bool
     */
    private function parseArgs($args)
    {
        if (!is_array($args)) {
            return false;
        }
        $components = array();
        if (count($args) == 1 && is_string($args[0])) {
            $components['href'] = $args[0];
            $components['rel'] = 'stylesheet';
            $components['type'] = 'text/css';
            return $this->makeLink($components);
        } elseif (count($args) == 1 && is_array($args)) {
            $components = $args[0];
            return $this->makeLink($components);
        } elseif (count($args) == 2 && is_string($args[0]) && is_array($args[1])) {
            $args[1]['href'] = $args[0];
            $components = $args[1];
            return $this->makeLink($components);
        }
        Jasper::i()->log->warning('Unable to interpret stylesheet. Not added.');
        return false;
    }

    /**
     * Generate a link to the stylesheet based on the passed parameters.
     *
     * @param $args
     *
     * @return bool|string
     */
    private function makeLink($args)
    {
        if (!isset($args['href'])) {
            Jasper::i()->log->warning('Unable to add stylesheet, no href attribute.');
            return false;
        }
        if (!isset($args['rel'])) {
            $args['rel'] = 'stylesheet';
        }
        if (!isset($args['type']) && $args['rel'] == 'stylesheet') {
            $args['type'] = 'text/css';
        }

        $link = '<link';
        foreach ($args as $key => $value) {
            $link .= ' ' . $key . '="' . $value . '"';
        }
        $link .= ' />';
        return $link;
    }
}