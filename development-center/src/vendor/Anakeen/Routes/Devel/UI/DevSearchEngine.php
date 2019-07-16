<?php


namespace Anakeen\Routes\Devel\UI;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\Utils\Strings;
use Anakeen\Search\SearchElements;
use Anakeen\Router\ApiV2Response;
use Slim\Http\request;
use Slim\Http\response;

class DevSearchEngine
{
    private $filters;
    private $take;
    private $skip;
    private $total;
    private $linkRules;

    public function __invoke(request $request, response $response, $args)
    {
        $this->linkRules = new DevSearchEngineLinkRules();
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(request $request, $args)
    {
        if ($request->getQueryParam("filter") && isset($request->getQueryParam("filter")["filters"])) {
            $this->filters = $request->getQueryParam("filter")["filters"];
        }
        $this->take = intval($request->getQueryParam("take", 20));
        $this->skip = intval($request->getQueryParam("skip", 0));
    }

    public function doRequest()
    {
        $data["data"] = array();
        $this->searchAll($this->filters, $data["data"]);
        if (isset($this->total)) {
            $data["total"] = $this->total;
        }
        return $data;
    }

    private function searchAll($filters, &$resultArray)
    {
        if (!empty($filters)) {
            //Search in all properties
            $searchString = Strings::unaccent(strtolower(trim($filters[0]["value"])));

            $searchElement = new SearchElements();
            $searchElement->setSlice($this->take);
            $searchElement->setStart($this->skip);
            if (is_numeric($searchString)) {
                $searchElement->addFilter(
                    "initid='%s' or upper(name) like upper('%s') or upper(title) like upper('%s')",
                    $searchString,
                    '%' . $searchString . '%',
                    '%' . $searchString . '%'
                );
            } else {
                $searchElement->addFilter("upper(name) like upper('%s') or upper(title) like upper('%s')", '%' . $searchString . '%', '%' . $searchString . '%');
            }

            //Set additional filters
            $additionalFilters = array_slice($filters, 1);
            if (!empty($additionalFilters)) {
                $sskeyword = Strings::unaccent(strtolower("SmartStructure"));
                foreach ($additionalFilters as $additionalFilter) {
                    $field = $additionalFilter["field"];
                    $value = Strings::unaccent(strtolower(trim($additionalFilter["value"])));
                    if (strcmp($field, "id") === 0 || strcmp($field, "initId") === 0) {
                        if (is_numeric($value)) {
                            $searchElement->addFilter("%s='%s'", $field, $value);
                        }
                    } elseif ($field === "fromname") {
                        $diplayOnlyStructures = ($value == $sskeyword);
                        $structureSearch = new SearchElements(-1);
                        if ($diplayOnlyStructures) {
                            $searchElement->addFilter("doctype='C'");
                        } else {
                            $structureSearch->addFilter("upper(name) like upper('%s')", '%' . $value . '%');
                            $structureResults = $structureSearch->search()->getResults();
                            if ($structureResults->count() != 0) {
                                $structureIds = array();
                                foreach ($structureResults as $id => $smartStructure) {
                                    array_push($structureIds, $id);
                                }
                                $searchElement->addFilter("fromid in (%s)", implode(",", $structureIds));
                            } else {
                                $emptyResult["data"] = array();
                                return $emptyResult;
                            }
                        }
                    } else {
                        $searchElement->addFilter("upper(%s) like upper('%s')", $field, '%' . $value . '%');
                    }
                }
            }

            $this->total = $searchElement->onlyCount();
            $searchElement->setOrder("name asc, title asc, id asc");
            $listResult = $searchElement->search()->getResults();
            foreach ($listResult as $id => $smartElement) {
                array_push($resultArray, $this->buildResult($smartElement));
            }
        }
    }

    /**
     * @param SmartElement $smartElement
     * @return mixed
     */
    private function buildResult(SmartElement $smartElement)
    {
        $docType = $smartElement->defDoctype;
        if ($docType  === "C") {
            $result["fromname"] = "SmartStructure";
        } else {
            $result["fromname"] = $smartElement->fromname;
        }
        $result["id"] = $smartElement->getPropertyValue("id");
        $result["name"] = $smartElement->getPropertyValue("name");
        $result["title"] = $smartElement->getPropertyValue("title");
        $result["links"] = $this->linkRules->getLinks($smartElement);
        return $result;
    }
}
