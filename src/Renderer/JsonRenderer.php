<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;

class JsonRenderer extends DownloadableRenderer
{
    protected $contentType = 'application/json; charset=utf-8';
    protected $extension = 'json';

    public function render(Response $response): void
    {
        parent::render($response);
        $this->getHeaders();
        // Assemble the values and output
        echo json_encode($response->getData(), JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
    }
}