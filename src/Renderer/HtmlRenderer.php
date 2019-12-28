<?php
namespace WigeDev\JasperCore\Renderer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use WigeDev\JasperCore\Lifecycle\Response;

/**
 * Class HtmlRenderer
 *
 * Renders the response as an HTML document
 * TODO: Need to add the folder paths for rendering.
 * TODO: Make this use twig
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
        // Assemble the data to be displayed
        $output = $response->getVariables();
        $output['data'] = $response->getData();
        //$base = $this->getBaseURL(false);
        //$response->getViewHelper('meta')->prepend('base', $base);
        extract($response->getVariables(), EXTR_SKIP);
        $page_content = '';
        // Set up Twig
        $twig_loader = new FilesystemLoader();
        $twig = new Environment($twig_loader); //TODO: Add caching
        $twig_loader->addPath($response->getViewPath(), 'view');
        $twig_loader->addPath($response->getLayoutPath(), 'layout');
        $output['layout__file'] = 'layout::' . $response->getLayoutFile();
        // Render the document
        $twig->render($response->getViewFile(), $output);




        // Include the view
//        Core::i()->log->debug('Trying to load view ' . $response->getViewFolder() . $response->getViewFile());
//        if (file_exists($response->getViewFolder() . $response->getViewFile())) {
//            ob_start();
//            include($response->getViewFolder() . $response->getViewFile());
//            $page_content = ob_get_contents();
//            ob_end_clean();
//        } else {
//            Core::i()->log->info('Did not find the view file. ' . $response->getViewFolder() . $response->getViewFile());
//        }
//        // Include the layout
//        /** @noinspection PhpUndefinedConstantInspection */
//        Core::i()->log->debug('Trying to load layout ' . _SITE_PATH_ . DS . $response->getLayout());
//        /** @noinspection PhpUndefinedConstantInspection */
//        if (file_exists(_SITE_PATH_ . DS . '_framework' . DS . $this->layout)) {
//            ob_start();
//            /** @noinspection PhpUndefinedConstantInspection */
//            include(_SITE_PATH_ . DS . '_framework' . DS . $this->layout);
//            $page_content = ob_get_contents();
//            ob_end_clean();
//        } else {
//            Core::i()->log->warning('Did not find the layout file.');
//        }
//        echo $page_content;
//        // For the dev environment, output debug info
//        if ($_SESSION['debug'] === true) {
//            echo '<hr />Debug Info <ul style="background-color: #ffffff; text-align: left;">';
//            foreach (Core::i()->log->getEvents() as $event) {
//                $log_color = '#FF0400';
//                if (strpos($event, 'Debug: ') > 0) {
//                    $log_color = '#aaaaaa';
//                } elseif (strpos($event, 'Info: ') > 0) {
//                    $log_color = '#000000';
//                } elseif (strpos($event, 'Notice: ') > 0) {
//                    $log_color = '#A00400';
//                }
//                echo "<li style=\"color: {$log_color}\">$event</li>";
//            }
//            echo '</ul>';
//            //$this->config->debugShowConfig();
//        }
    }
}