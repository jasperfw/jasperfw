<?php
namespace WigeDev\JasperCore\Renderer;

use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Utility\Response;

/**
 * Class HtmlRenderer
 *
 * Renders the response as an HTML document
 * TODO: Need to add the folder paths for rendering.
 *
 * @package WigeDev\JasperCore\Renderer
 */
class HtmlRenderer extends Renderer
{
    public function render(Response $response): void
    {
        parent::render($response);
        //$base = $this->getBaseURL(false);
        /** @noinspection PhpUndefinedMethodInspection */
        //$response->getViewHelper('meta')->prepend('base', $base);
        extract($response->getVariables(), EXTR_SKIP);
        $page_content = '';
        // Include the view
        Core::i()->log->debug('Trying to load view ' . $response->getViewFolder() . $response->getViewFile());
        if (file_exists($response->getViewFolder() . $response->getViewFile())) {
            ob_start();
            include($response->getViewFolder() . $response->getViewFile());
            $page_content = ob_get_contents();
            ob_end_clean();
        } else {
            Core::i()->log->info('Did not find the view file. ' . $response->getViewFolder() . $response->getViewFile());
        }
        // Include the layout
        /** @noinspection PhpUndefinedConstantInspection */
        Core::i()->log->debug('Trying to load layout ' . __SITE_PATH__ . DS . $response->getLayout());
        /** @noinspection PhpUndefinedConstantInspection */
        if (file_exists(__SITE_PATH__ . DS . '_framework' . DS . $this->layout)) {
            ob_start();
            /** @noinspection PhpUndefinedConstantInspection */
            include(__SITE_PATH__ . DS . '_framework' . DS . $this->layout);
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