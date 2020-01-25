<?php
namespace JasperFW\JasperCore\Renderer;

use JasperFW\JasperCore\Lifecycle\Response;

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
        $variables['data'] = $response->getData();
        $variables['success'] = ($response->getStatusCode() === 200) ? 'OK' : 'Failure - ' . $response->getStatusCode();
        $variables['messages'] = $response->getMessages();
        $page_content = json_encode($variables, JSON_UNESCAPED_UNICODE);
        echo $page_content;
    }
}