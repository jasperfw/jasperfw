<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Jasper;
use JasperFW\JasperFW\Lifecycle\Response;

/**
 * Class HtmlRenderer
 *
 * Renders the response as an HTML document
 * TODO: Need to add the folder paths for rendering.
 * TODO: Test and finalize
 *
 * @package JasperFW\JasperFW\Renderer
 */
class HtmlRenderer extends Renderer
{
    /**
     * @param Response $response
     */
    public function render(Response $response): void
    {
        parent::render($response);
        $pageContent = '';
        // Include the view
        Jasper::i()->log->debug('Trying to load view ' . $response->getViewPath() . $response->getViewFile());
        if (file_exists($response->getViewPath() . DS . $response->getViewFile() . '.phtml')) {
            ob_start();
            include($response->getViewPath() . DS . $response->getViewFile() . ".phtml");
            $pageContent = ob_get_contents();
            ob_end_clean();
        } else {
            Jasper::i()->log->warning(
                'Did not find the view file. ' . $response->getViewPath() . $response->getViewFile()
            );
        }
        // Include the layout
        Jasper::i()->log->debug(
            'Trying to load layout ' . _SITE_PATH_ . DS . $response->getLayoutPath() . DS . $response->getLayoutFile()
        );
        if (file_exists($response->getLayoutPath() . DS . $response->getLayoutFile() . ".phtml")) {
            ob_start();
            include($response->getLayoutPath() . DS . $response->getLayoutFile() . ".phtml");
            $pageContent = ob_get_contents();
            ob_end_clean();
        } else {
            Jasper::i()->log->warning('Did not find the layout file.');
        }
        echo $pageContent;
        // For the dev environment, output debug info
    }
}