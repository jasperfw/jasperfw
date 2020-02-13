<?php

namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;

/**
 * Class AjaxRenderer
 *
 * This renderer outputs the data as a JSON object, in the "data" node. Additionally, outputs a "success" and "messages"
 * node, with "success" containing true or false depending on the success of the operation, and "messages" containing
 * any messages that were sent to the Resposne object.
 *
 * @package JasperFW\JasperFW\Renderer
 */
class AjaxRenderer extends Renderer
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