<?php
namespace WigeDev\JasperCore\Renderer;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;
use WigeDev\JasperCore\Lifecycle\Response;

/**
 * Class TwigRenderer
 *
 * Renders the response as an HTML document using the Twig template engine from Symphony
 * TODO: Need to add the folder paths for rendering.
 *
 * @package WigeDev\JasperCore\Renderer
 */
class TwigRenderer extends Renderer
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
        $output = $response->getValues();
        $output['data'] = $response->getData();
        //$base = $this->getBaseURL(false);
        //$response->getViewHelper('meta')->prepend('base', $base);
        //extract($response->getVariables(), EXTR_SKIP);
        // Set up Twig
        $twig_loader = new FilesystemLoader();
        $twig = new Environment($twig_loader); //TODO: Add caching
        $twig->addGlobal('renderer', $this);
        $twig->addExtension(new StringLoaderExtension());
        $twig_loader->addPath($response->getViewPath());
        $twig_loader->addPath($response->getLayoutPath()); //TODO: Add namespacing
        $output['layout__file'] = $response->getLayoutFile();
        // Render the document
        echo $twig->render($response->getViewFile(), $output);
    }
}