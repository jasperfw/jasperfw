<?php

namespace JasperFW\JasperFW\Renderer;

use JasperFW\JasperFW\Lifecycle\Response;
use function JasperFW\JasperFW\J;

/**
 * Class DownloadableRenderer
 *
 * Renderers that represent downloadable file types (ie, CSV, JSON, XML, etc) should extend this class which adds
 * functionality. The extending class should override the file extension and content type properties.
 *
 * @package JasperFW\JasperFW\Renderer
 */
abstract class DownloadableRenderer extends Renderer
{
    protected $extension = '';
    protected $contentType = 'text/text';

    public function render(Response $response): void
    {
        parent::render($response);
        $this->setHeaders();
    }

    protected function setHeaders()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: ' . $this->contentType);
        if (J()->response->isDownload()) {
            if (J()->response->getDownloadFileName()) {
                $filename = J()->response->getDownloadFileName();
            } else {
                $filename = J()->request->getFilename();
            }
            if (!empty($this->extension)) {
                $filename .= '.' . $this->extension;
            }
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
    }
}
