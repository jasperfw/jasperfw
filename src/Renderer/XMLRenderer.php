<?php

namespace JasperFW\JasperFW\Renderer;

use JasperFW\Core\XML\Array2XML;
use JasperFW\JasperFW\Lifecycle\Response;

class XMLRenderer extends DownloadableRenderer
{
    protected string $contentType = 'application/xml; charset=utf-8';
    protected string $extension = 'xml';

    public function render(Response $response): void
    {
        parent::render($response);
        // Assemble the values and output
        $rootElement = $response->getValues()['rootElement'] ?? 'root';
        $xml = Array2XML::createXML($rootElement, $response->getData());
        echo $xml->saveXML();
    }
}
