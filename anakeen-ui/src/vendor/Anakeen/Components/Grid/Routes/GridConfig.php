<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 07/08/18
 * Time: 13:53
 */

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;
use Anakeen\Core\SmartStructure;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use SmartStructure\Fields\Search;
use SmartStructure\Fields\Dir;

/**
 * Config for Smart Element Grid
 *
 * Class Config
 *
 * @note    Used by route : GET api/v2/grid/config
 * @package Anakeen\Routes\Authent
 */
class GridConfig
{

    const DEFAULT_COLUMNS = ["icon", "title"];

    protected $gridFields = [];
    protected $urlFields = [];
    protected $collectionId = null;

    /**
     * @var SmartElement
     */
    protected $collectionDoc = null;

    /**
     * @var SmartStructure
     */
    protected $structureRef = null;
    protected $structureId = null;

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $this->parseRequestParams($request, $response, $args);
        $this->gridFields = $this->getGridFields();

        return ApiV2Response::withData($response, $this->getConfig());
    }

    protected function parseRequestParams(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)  {
        $this->collectionId = $args["collectionId"];
        $this->collectionDoc = SmartElementManager::getDocument($this->collectionId);
        if (!$this->collectionDoc) {
            $exception = new Exception("GRID0001", $this->collectionId);
            $exception->setHttpStatus("404", "Smart Element not found");
            throw $exception;
        }
        $urlFieldsParam = $request->getQueryParam("fields", "");
        if (!empty($urlFieldsParam)) {
            $this->urlFields = array_map("trim", explode(",", $urlFieldsParam));
        }
    }

    protected function getConfig() {
        return array(
            "smartFields" => $this->gridFields,
            "footer" => array(),
            "toolbar" => [],
            "actions" => [],
            "contentURL" => sprintf("/api/v2/grid/content/%s%s", $this->collectionId, "?fields=".$this->getUrlFields())
        );
    }

    protected function getUrlFields() {
        $filteredAttributes = array_filter($this->gridFields, function ($field) {
            return ($field["type"] !== 'array' && $field["type"] !== 'tab' && $field["type"] !== 'frame');
        });
        $result = implode(',', array_map(function ($field) {
            if ($field["property"]) {
                return "document.properties.".$field["field"];
            }
            return "document.attributes.".$field["field"];
        }, $filteredAttributes));
        return $result;
    }

    public function getGridFields()
    {
        switch ($this->collectionDoc->defDoctype) {
            case "C": // Smart Structure
                $this->structureId = $this->collectionDoc->initid;
                break;
            case "D": // Dir
                $this->structureId = $this->collectionDoc->fromid;
                break;
            case "S": // Search
                $this->structureId = $this->collectionDoc->getRawValue(Search::se_famid);
                if (empty($this->structureId)) {
                    return ColumnsConfig::getCollectionAvailableFields($this->collectionDoc, null, $this->urlFields);
                }
                break;
        }
        $this->structureRef = SmartElementManager::getFamily($this->structureId);
        if (!$this->structureRef) {
            $exception = new Exception("GRID0002", $this->structureId);
            $exception->setHttpStatus("404", "Searched Smart Structure not found");
            throw $exception;
        }
        return ColumnsConfig::getCollectionAvailableFields($this->collectionDoc, $this->structureRef, $this->urlFields);
    }
}