<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;

class CLIRenderer extends Renderer
{
    public function render(Response $response): void
    {
        echo "Variables\n";
        var_dump($response->getVariables());
        echo "Data\n";
        var_dump($response->getData());
        echo "Messages\n";
        var_dump($response->getMessages());
        //echo "Debug\n";
        //var_dump(Jasper::i()->log);
    }
}
