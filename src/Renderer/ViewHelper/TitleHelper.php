<?php
/**
 * Created by IntelliJ IDEA.
 * User: inter
 * Date: 10/11/2018
 * Time: 11:33 PM
 */

namespace JasperFW\JasperCore\Renderer\ViewHelper;


use JasperFW\JasperCore\Jasper;

/**
 * Class TitleHelper
 *
 * The title helper controls the display of page titles. This can be used to set the page title, or the title of the
 * site.
 */
class TitleHelper extends ViewHelper
{
    /** @var  string The name of the site, usually obtained from the config files. */
    protected $site_name;
    /** @var  string The name of the page, usually set by the module controller or a model. */
    protected $page_name;
    /** @var  string The format of the page title */
    protected $title_format = ':page_name: | :site_name:';

    /**
     * The init function is called when a view helper is loaded. This function should look in the configuration to find
     * any initial settings for the view helper.
     *
     * @param string|null $configuration The name of the settings in the config array
     */
    public function init(?string $configuration = 'project'): void
    {
        $config = Jasper::i()->config->getConfiguration($configuration);
        if (isset($config['site_name'])) {
            $this->site_name = $config['site_name'];
        }
        if (isset($config['title_format'])) {
            $this->title_format = $config['title_format'];
        }
    }

    /**
     * @param string $name
     */
    public function setSiteName(string $name): void
    {
        $this->site_name = $name;
    }

    /**
     * @return string The name of the site
     */
    public function getSiteName(): string
    {
        return $this->site_name;
    }

    /**
     * @param string $name The name of the page
     */
    public function setPageName(string $name): void
    {
        $this->page_name = $name;
    }

    /**
     * @return string The name of the page
     */
    public function getPageName(): string
    {
        return $this->page_name;
    }

    /**
     * Override this method to display the values.
     *
     * @return string A string representation of the title
     */
    public function __toString(): string
    {
        $return = $this->title_format;
        $return = str_replace(':page_name:', $this->page_name, $return);
        $return = str_replace(':site_name:', $this->site_name, $return);
        return $return;
    }
}