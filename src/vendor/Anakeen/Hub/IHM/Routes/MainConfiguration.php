<?php

namespace Anakeen\Hub\IHM\Routes;

use Anakeen\Core\ContextManager;

class MainConfiguration
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $search = new \SearchDoc("", "HUBCONFIGURATION");
        $search->setObjectReturn(true);
        $search->search();
        $documentList = $search->getDocumentList();
        $return = [];
        foreach ($documentList as $document) {
           $return[] = $document->getConfiguration();
        }
        return $response->withJson($return);
    }
}