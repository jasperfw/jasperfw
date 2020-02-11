<?php
namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;

use function JasperFW\JasperFW\J;

class CSVRenderer extends Renderer
{
    public function render(Response $response): void
    {
        var_dump(J()->request->getFilename());
        parent::render($response);
        // Set the headers for a CSV response
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: text/csv; charset=utf-8');
        // Make sure a proper file extension will be set regardless of the filename.
        $filename = J()->request->getFilename() . '.';
        if (empty(J()->request->getExtension())) {
            $filename .= 'csv';
        } else {
            $filename .= J()->request->getExtension();
        }
        header('Content-Disposition: attachment; filename="' . $filename . '"');
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