<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;

class CSVRenderer extends DownloadableRenderer
{
    protected $contentType = 'text/csv';
    protected $extension = 'csv';

    public function render(Response $response): void
    {
        parent::render($response);
        // Create an output stream to write to
        $file = fopen('php://output', 'w');
        // Assemble the values and output
        if (isset($response->getValues()['headers'])) {
            fputcsv($file, $response->getValues()['headers']);
        }
        foreach ($response->getData() as $row) {
            fputcsv($file, $row);
        }
        exit();
    }
}
