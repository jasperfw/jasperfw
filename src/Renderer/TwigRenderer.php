<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\StringLoaderExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Class TwigRenderer
 *
 * Renders the response as an HTML document using the Twig template engine from Symphony
 *
 * @package JasperFW\JasperFW\Renderer
 */
class TwigRenderer extends Renderer
{
    /** @var Environment */
    protected $twig;
    /** @var FilesystemLoader */
    protected $twig_loader;

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
        $output['_data'] = $response->getData();
        $output['_messages'] = $response->getMessages();
        //$base = $this->getBaseURL(false);
        //$response->getViewHelper('meta')->prepend('base', $base);
        //extract($response->getVariables(), EXTR_SKIP);
        // Set up Twig
        $twig = $this->initTwig($response);
        $output['layout__file'] = $response->getLayoutFile() . '.twig';
        // Render the document
        echo $twig->render($response->getViewFile() . '.twig', $output);
    }

    protected function initTwig(Response $response): Environment
    {
        $this->twig_loader = new FilesystemLoader();
        $this->twig = new Environment($this->twig_loader); //TODO: Add caching
        $this->twig->addGlobal('renderer', $this);
        $this->twig->addExtension(new StringLoaderExtension());
        $this->twig_loader->addPath($response->getViewPath());
        $this->twig_loader->addPath($response->getLayoutPath()); //TODO: Add namespacing
        $this->parseFilters($response);
        return $this->twig;
    }

    protected function parseFilters(Response $response)
    {
        $filter_class_name = TwigFilter::class;
        if (is_array($response->getValues()['twigFilters'])) {
            foreach ($response->getValues()['twigFilters'] as $filter) {
                if ($filter instanceof $filter_class_name) {
                    $this->twig->addFilter($filter);
                    //var_dump('Added.');
                }
            }
        }
    }

}