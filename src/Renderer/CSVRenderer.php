<?php
namespace JasperFW\JasperCore\Renderer;

use JasperFW\JasperCore\Lifecycle\Response;

class CSVRenderer extends Renderer
{
    public function render(Response $response): void
    {
        parent::render($response);
        // Set the headers for a CSV response
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: text/csv; charset=utf-8');
        // Create an output stream to write to
        $file = fopen('php://output', 'w');
        // Assemble the values and output
        if (isset($response->getVariables()['headers'])) {
            fputcsv($file, $response->getVariables()['headers']);
        }
        foreach ($response->getData() as $row) {
            fputcsv($file, $row);
        }
        exit();
    }
}