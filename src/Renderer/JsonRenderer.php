<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;

class JsonRenderer extends DownloadableRenderer
{
    protected string $contentType = 'application/json; charset=utf-8';
    protected string $extension = 'json';

    public function render(Response $response): void
    {
        parent::render($response);
        // Assemble the values and output
        $output = $response->getValues();
        if (count($response->getMessages()) > 0) {
            $output['messages'] = $response->getMessages();
        }
        if (!is_null($response->getData())) {
            $output['data'] = $response->getData();
        }
        echo json_encode($output, JSON_UNESCAPED_UNICODE);
    }
}
