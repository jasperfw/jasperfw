<?php
/**
 * Created by IntelliJ IDEA.
 * User: inter
 * Date: 10/11/2018
 * Time: 8:08 PM
 */

namespace JasperFW\JasperFW\Renderer\ViewHelper;

class MetaHelper extends ViewHelperCollection
{
    /**
     * Add a meta tag to the beginning of the collection. Accepts two arguments, the
     *
     * @param string ... The name
     * @param string ... The content
     */
    public function prepend()
    {
        if (func_num_args() == 2) {
            parent::prepend(func_get_arg(0), func_get_arg(1));
        }
    }

    /**
     * Add a meta tag to the end of the collection
     * @param string ... The name
     * @param string ... The content
     */
    public function append()
    {
        if (func_num_args() == 2) {
            parent::append(func_get_arg(0), func_get_arg(1));
        }
    }

    /**
     * Provides a quick way to set a value. Tags specified this way are appended to the collection.
     * @param $name
     * @param $content
     */
    public function __set($name, $content)
    {
        if ($name == 'base') return;
        $this->append($name, $content);
    }

    /**
     * Return the value of the current member of the collection. Styling or formatting of the element should be done
     * in this function.
     * @return mixed
     */
    public function current()
    {
        [$name, $content] = $this->members[$this->pointer];
        if (is_a($content, ViewHelperInterface::class)) {
            /** @var $content ViewHelperInterface */
            return $content->__toString();
        }
        switch ($name) {
            case 'charset':
                return "<meta charset=\"{$content}\">";
            case 'base':
                return "<base href=\"{$content}\" />";
            default:
                return "<meta name=\"{$name}\" content=\"{$content}\">";
        }
    }

    /**
     * Override this method to display all the values. Typically, this will be done by performing a foreach on the
     * object so that current is called to return properly formatted strings. For example, if the child were being used
     * to generate an HTML list, the toString method would output <li> and </li> tags around a foreach loop of $this.
     * @return mixed
     */
    public function __toString() : string
    {
        $return = '';
        foreach ($this as $line) {
            $return .= $line . "\n";
        }
        return $return;
    }
}