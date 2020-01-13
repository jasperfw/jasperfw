<?php
namespace WigeDev\JasperCore\Renderer;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Lifecycle\Response;

/**
 * Class HtmlRenderer
 *
 * Renders the response as an HTML document
 * TODO: Need to add the folder paths for rendering.
 * TODO: Test and finalize
 *
 * @package WigeDev\JasperCore\Renderer
 */
class HtmlRenderer extends Renderer
{
    /**
     * @param Response $response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(Response $response): void
    {
        parent::render($response);
        // Include the view
        Core::i()->log->debug('Trying to load view ' . $response->getViewPath() . $response->getViewFile());
        if (file_exists($response->getViewPath() . DS . $response->getViewFile() . '.phtml')) {
            ob_start();
            include($response->getViewPath() . DS . $response->getViewFile() . ".phtml");
            $page_content = ob_get_contents();
            ob_end_clean();
        } else {
            Core::i()->log->info('Did not find the view file. ' . $response->getViewPath() . $response->getViewFile());
        }
        // Include the layout
        Core::i()->log->debug(
            'Trying to load layout ' . _SITE_PATH_ . DS . $response->getLayoutPath() . DS . $response->getLayoutFile()
        );
        if (file_exists($response->getLayoutPath() . DS . $response->getLayoutFile() . ".phtml")) {
            ob_start();
            include($response->getLayoutPath() . DS . $response->getLayoutFile() . ".phtml");
            $page_content = ob_get_contents();
            ob_end_clean();
        } else {
            Core::i()->log->warning('Did not find the layout file.');
        }
        echo $page_content;
        // For the dev environment, output debug info
        if ($_SESSION['debug'] === true) {
            echo '<hr />Debug Info <ul style="background-color: #ffffff; text-align: left;">';
            foreach (Core::i()->log->getEvents() as $event) {
                $log_color = '#FF0400';
                if (strpos($event, 'Debug: ') > 0) {
                    $log_color = '#aaaaaa';
                } elseif (strpos($event, 'Info: ') > 0) {
                    $log_color = '#000000';
                } elseif (strpos($event, 'Notice: ') > 0) {
                    $log_color = '#A00400';
                }
                echo "<li style=\"color: {$log_color}\">$event</li>";
            }
            echo '</ul>';
            //$this->config->debugShowConfig();
        }
    }
}