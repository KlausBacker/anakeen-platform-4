<?php

namespace Anakeen\Routes\Core;

use Dcp\ExportDocument;

class DocumentDataCSV extends DocumentData
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        parent::__invoke($request, $response, $args);
        $contentType = 'text/csv';
        if ($request->getQueryParam("inline") !== null) {
            $contentType = "text/plain";
        }
        return $response->withHeader('Content-Type', $contentType)->write($this->getDocumentData());
    }

    /**
     * Get document data
     *
     */
    protected function getDocumentData()
    {
        $exportXML = new ExportDocument();
        $useless = [];
        $file = fopen('php://memory', 'w+');
        $exportXML->csvExport($this->_document, $useless, $file, false, false, false, true, true, "I");
        rewind($file);
        $contents = stream_get_contents($file);
        fclose($file);
        return $contents;
    }
}