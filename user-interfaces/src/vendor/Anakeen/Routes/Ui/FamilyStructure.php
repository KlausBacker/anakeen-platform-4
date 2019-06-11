<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class FamilyStructure
 * @note Used by route : GET /api/v2/smart-structures/{family}/views/structure
 * @package Anakeen\Routes\Ui
 */
class FamilyStructure extends \Anakeen\Routes\Core\DocumentData
{


    public function __construct()
    {
        parent::__construct();
        $this->defaultFields = self::GET_STRUCTURE;
    }

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $resourceId = $args["family"];

        $this->_document=SEManager::getFamily($resourceId);
        if (!$this->_document) {
            throw new \Anakeen\Ui\Exception("CRUDUI0013", $resourceId);
        }

        $etag = $this->getDocumentEtag($this->_document->id);
        $response = ApiV2Response::withEtag($request, $response, $etag);
        if (ApiV2Response::matchEtag($request, $etag)) {
            return $response;
        }

        return ApiV2Response::withData($response, $this->getDocumentData());
    }

    protected function getDocumentApiData()
    {
        return new FamilyApiData($this->_document);
    }

    /**
     * Compute etag from an id
     *
     * @param $id
     *
     * @return string
     * @throws \Anakeen\Database\Exception
     */
    protected function extractEtagDataFromId($id)
    {
        $result = array();
        $sql = sprintf("select mdate from docfam where id = %d", $id);

        DbManager::query($sql, $result, false, true);
        // Necessary only when use family.structure
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG");
        $result[] = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION");
        return join(" ", $result);
    }
}
