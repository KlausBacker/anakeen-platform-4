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

use SmartStructure\Fields\Report as ReportFields;

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

    protected function parseRequestParams(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
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

    protected function getConfig()
    {
        return array(
            "smartFields" => $this->gridFields,
            "pageable" => $this->getPageable(),
            "footer" => array(),
            "toolbar" => [],
            "actions" => [],
            "locales" => $this->getLocales(),
            "collection"=> [
                "id" => $this->collectionDoc->initid,
                "name" => $this->collectionDoc->name,
                "title" => $this->collectionDoc->getTitle(),
            ],
            "contentURL" => sprintf("/api/v2/grid/content/%s%s", $this->collectionId, "?fields=" . $this->getUrlFields())
        );
    }

    protected function getPageable()
    {
        if ($this->collectionDoc) {
            $pageSlice = $this->collectionDoc->getRawValue(ReportFields::rep_limit);
            if ($pageSlice) {
                return ["pageSize" => intval($pageSlice), "pageSizes" => [intval($pageSlice)]];
            }
        }
        return null;
    }

    protected function getLocales()
    {
        return [
            "pageable" => [
                "messages" => [
                    "itemsPerPage" => ___("results per page", "smart-grid"),
                    "of" => ___("of", "smart-grid"),
                    "display" => ___("{0} - {1} of {2} results", "smart-grid"),
                    "empty" => ___("No results", "smart-grid")
                ],
            ],
            "filterable" => [
                "messages" => [
                    "and" => ___("And", "smart-grid"),
                    "clear" => ___("Clear", "smart-grid"),
                    "filter" => ___("Filter", "smart-grid"),
                ],
            ],
            "consult" => ___("Display", "smart-grid"),
            "edit" => ___("Modify", "smart-grid"),
            "export" => ___("Export as XLSX", "smart-grid"),
            "selectOperator" => ___("-- Select another operator --", "smart-grid"),
            "extraOperator" => ___("Grid Settings", "smart-grid")
        ];
    }

    protected function getUrlFields()
    {
        $filteredAttributes = array_filter($this->gridFields, function ($field) {
            $type = $field["smartType"] ?: $field["type"];
            return ($type !== 'array' && $type !== 'tab' && $type !== 'frame' && empty($field["abstract"]));
        });
        $result = implode(',', array_map(function ($field) {
            if (!empty($field["property"])) {
                return "document.properties." . $field["field"];
            }
            return "document.attributes." . $field["field"];
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
