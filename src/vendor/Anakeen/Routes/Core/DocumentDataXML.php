<?php

namespace Anakeen\Routes\Core;

use Dcp\ExportXmlDocument;

class DocumentDataXML extends DocumentData
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        parent::__invoke($request, $response, $args);
        return $response->withHeader('Content-Type', 'text/xml')
            ->write($this->getDocumentData());
    }

    /**
     * Get document data
     *
     */
    protected function getDocumentData()
    {
        $exportXML = new ExportXmlDocument();
        $exportXML->setDocument($this->_document);
        return $exportXML->getXml();
    }
}