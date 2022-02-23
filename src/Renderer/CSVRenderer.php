<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;
use JetBrains\PhpStorm\NoReturn;

class CSVRenderer extends DownloadableRenderer
{
    protected string $contentType = 'text/csv';
    protected string $extension = 'csv';

    #[NoReturn] public function render(Response $response): void
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
