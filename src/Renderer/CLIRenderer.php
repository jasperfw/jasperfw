<?php
namespace JasperFW\JasperCore\Renderer;


use JasperFW\JasperCore\Jasper;
use JasperFW\JasperCore\Lifecycle\Response;

class CLIRenderer extends Renderer
{
    public function render(Response $response) : void
    {
        echo "Variables\n";
        var_dump($response->getVariables());
        echo "Messages\n";
        var_dump($response->getMessages());
        echo "Debug\n";
        var_dump(Jasper::i()->log);
    }
}