<?php
namespace WigeDev\JasperCore\ServiceManager;

use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Renderer\Renderer;

/**
 * Class ViewManager
 *
 * The ViewManager handles setting up the view based on the routing. This is a service manager instead of built into
 * the Framework because variables can be written to the view from almost anywhere.
 *
 * @package WigeDev\JasperCore\ServiceManager
 */
class ViewManager
{
    // Config settings
    protected $renderers = array();
    protected $default_country;
    protected $default_locale;
    protected $default_view_type;
    protected $default_layout;

    // Settings
    protected $country;
    protected $locale;
    protected $view_type;

    /** @var Renderer A reference to the renderer that will be rendering the view */
    protected $renderer;

    public function init() : void
    {
        // Load the variables from the configuration manager
        $config = Core::i()->config->getConfiguration('view');
        foreach ($config as $key => $configuration) {
            if ($key === 'renderers') {
                $this->renderers = $configuration;
            } else {
                $this->__set($key, $configuration);
            }
        }
        $this->determineViewType();
    }
}