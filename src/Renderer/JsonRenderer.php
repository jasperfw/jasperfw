<?php
namespace WigeDev\JasperCore\Renderer;


use WigeDev\JasperCore\Core;
use WigeDev\JasperCore\Utility\Response;

class JsonRenderer extends Renderer
{
    public function render(Response $response): void
    {
        parent::render($response);
        // Set the headers for a JSON response
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json; charset=utf-8');
        // Assemble the values and output
        $variables = $response->getVariables();
        $variables['success'] = $response->getStatusCode();
        $variables['messages'] = $response->getMessages();
        // For the dev environment, add the debug information to the variables array
        if ($_SESSION['debug'] === true) {
            $variables['debug'] = Core::i()->log->getEventArray();
        }
        $page_content = json_encode($variables, JSON_UNESCAPED_UNICODE);
        echo $page_content;
    }
}