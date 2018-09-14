<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 07/08/18
 * Time: 13:53
 */

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\NormalAttribute;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;
use SmartStructure\Fields\Search;
use SmartStructure\Fields\Report;
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
    protected $collectionDoc = null;

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)
    {
        $this->parseRequestParams($request, $response, $args);
        $this->gridFields = self::getGridFields($this->collectionDoc, $this->urlFields);

        return ApiV2Response::withData($response, $this->getConfig());
    }

    protected function parseRequestParams(\Slim\Http\Request $request, \Slim\Http\Response $response, $args)  {
        $this->collectionId = $args["collectionId"];
        $this->collectionDoc = SEManager::getDocument($this->collectionId);
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

    public static function getGridFields(\Anakeen\Core\Internal\SmartElement $smartElement, $returnsOnly = [])
    {
        $smartStructureId = -1;
        switch ($smartElement->defDoctype) {
            case "C": // Smart Structure
                $smartStructureId = $smartElement->initid;
                break;
            case "D": // Dir
                $smartStructureId = $smartElement->getRawValue(Dir::fld_famids);
                break;
            case "S": // Search
                $smartStructureId = $smartElement->getRawValue(Search::se_famid);
                if (empty($smartStructureId)) {
                    return ColumnsConfig::getCollectionAvailableFields($smartElement, null, $returnsOnly);
                }
                break;
        }
        $smartStructure = SEManager::getFamily($smartStructureId);
        if (!$smartStructure) {
            $exception = new Exception("GRID0002", $smartStructureId);
            $exception->setHttpStatus("404", "Searched Smart Structure not found");
            throw $exception;
        }
        return ColumnsConfig::getCollectionAvailableFields($smartElement, $smartStructure, $returnsOnly);
    }
}