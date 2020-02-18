<?php

namespace Anakeen\Components\Grid;

use Anakeen\Components\Grid\Exceptions\Exception;
use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Routes\Core\Lib\DocumentDataFormatter;
use Anakeen\Search\ElementList;
use Anakeen\Search\SearchElements;
use Anakeen\SmartElementManager;
use Closure;
use SmartStructure\Fields\Search;

class SmartGridContentBuilder
{
    /**
     * @var SearchElements
     */
    protected $searchElements = null;

    protected $fields = [];
    protected $sort = [];

    protected $smartCollectionId = null;

    /**
     * @var Filter data information
     */
    protected $filter = null;

    /**
     * Page number
     * @var int
     */
    protected $page = 1;

    /**
     * Page size
     * @var int
     */
    protected $pageSize = 10;

    /**
     * Collection element
     * @var SmartElement
     */
    protected $smartCollection = null;

    /**
     * Constructor of SmartGridContentBuilder
     *
     * @param  mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     *
     * @return $this - the current instance
     */
    public function __construct($collectionId = 0)
    {
        $this->setCollection($collectionId);
    }

    /**
     * Set the smart collection which the smart element Smart Element Grid is based on
     *
     * @param  mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     * @return $this - the current instance
     */
    public function setCollection($collectionId)
    {
        if ($collectionId !== 0 && $collectionId !== -1) {
            $this->smartCollection = SEManager::getDocument($collectionId);
            if (!$this->smartCollection) {
                $exception = new Exception("GRID0001", $this->collectionId);
                $exception->setHttpStatus("404", "Smart Element not found");
                throw $exception;
            }


            $error = $this->smartCollection->control("execute");
            if ($error) {
                $exception = new Exception("GRID0015", $this->collectionId);
                $exception->setHttpStatus("403", "Insufficient privileges");
                throw $exception;
            }
            switch ($this->smartCollection->defDoctype) {
                case 'C':
                    $this->initSearch($collectionId);
                    break;
                case 'D':
                    $this->initSearch();
                    $this->searchElements->useCollection($collectionId);
                    break;
                case 'S':
                    $fromId = $this->smartCollection->getRawValue(Search::se_famid, 0);
                    $this->initSearch($fromId);
                    $this->searchElements->useCollection($collectionId);
                    break;
            }
        } else {
            $this->initSearch($collectionId);
        }
    }

    public function addSort($colId, $direction)
    {
        $this->sort[] = ["field" => $colId, "dir" => $direction];
        $order = implode(",", array_map(function ($sort) {
            return $sort["field"] . " " . $sort["dir"];
        }, $this->sort));
        $order .= ",id ASC";
        $this->searchElements->setOrder($order);
    }

    public function addFilter($filter)
    {
        if (!empty($filter)) {
            // First need flat filters
            $flatFilters = static::getFlatLevelFilters($filter);

            foreach ($flatFilters as $filter) {
                $filterObject = Operators::getFilterObject($filter);
                if (!empty($filterObject)) {
                    $this->searchElements->addFilter($filterObject);
                }
            }
        }
        return $this;
    }

    public function addAbstract($colId, Closure $dataFunction)
    {
        $this->fields[] = [
            "field" => $colId,
            "property" => false,
            "abstract" => true,
            "dataFunction" => $dataFunction
        ];
        return $this;
    }

    public function addField($fieldId)
    {
        $this->fields[] = [
            "field" => $fieldId,
            "property" => false,
            "abstract" => false
        ];
        return $this;
    }

    public function addProperty($propId)
    {
        $this->fields[] = [
            "field" => $propId,
            "property" => true,
            "abstract" => false
        ];
        return $this;
    }

    public function addColumn($field)
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Set custom search
     *
     * @param  SearchElements $search
     *
     * @return $this - the current instance
     */
    public function setSearch(SearchElements $search)
    {
        $this->searchElements = $search;
        return $this;
    }

    /**
     * Returns the content data
     *
     * @return array - the data
     */
    public function getContent()
    {
        $return = [];
        if (ContextManager::getParameterValue("Ui", "MODE_DEBUG", false)) {
            $return["debug"] = $this->searchElements->getSearchInfo();
        }
        $return["requestParameters"] = $this->getRequestParameters();
        $return["content"] = [];
        foreach ($this->searchElements->getResults() as $element) {
            $return["content"][] = $this->formatElement($element);
        }
        return $return;
    }

    public function setPage($page)
    {
        $this->page = $page;
        $this->setPager($this->page, $this->pageSize);
    }


    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        $this->setPager($this->page, $this->pageSize);
    }

    protected static function getFlatLevelFilters($filterArg)
    {
        $flatFilters = [];
        if (!empty($filterArg["filters"])) {
            foreach ($filterArg["filters"] as $filterElement) {
                $flatFilters = array_merge($flatFilters, static::getFlatLevelFilters($filterElement));
            }
        } elseif (empty($filterArg["logic"]) && !empty($filterArg["field"])) {
            $flatFilters[] = $filterArg;
        }
        return $flatFilters;
    }

    protected function getRequestParameters()
    {
        return [
            "pager" => [
                "page" => $this->page,
                "skip" => $this->pageSize !== "ALL" ? ($this->page * $this->pageSize) - $this->pageSize : 0,
                "take" => $this->pageSize,
                "pageSize" => $this->pageSize === "ALL" ? 10 : $this->pageSize,
                "total" => $this->searchElements->onlyCount()
            ]
        ];
    }

    protected function initSearch($structureId = 0)
    {
        $this->searchElements = new SearchElements($structureId);
        $this->searchElements->excludeConfidential(true);
        $this->setPager($this->page, $this->pageSize);
    }

    protected function setPager($page, $pageSize)
    {
        $this->searchElements->setSlice($pageSize);
        if ($pageSize !== "ALL") {
            $this->searchElements->setStart(($page - 1) * $pageSize);
        }
    }
    protected function formatElement(SmartElement $element)
    {
        $df = new DocumentDataFormatter($element);
        foreach ($this->fields as $field) {
            if (isset($field["field"])) {
                if (isset($field["property"]) && $field["property"]) {
                    $df->addProperty($field["field"]);
                } elseif (isset($field["abstract"]) && $field["abstract"]) {
                    $abstractDataFunction = function () {
                        return [
                            "value" => null,
                            "displayValue" => null
                        ];
                    };
                    if (isset($field["dataFunction"]) && is_callable($field["dataFunction"])) {
                        $abstractDataFunction = $field["dataFunction"];
                    }
                    $df->getFormatCollection()->setDocumentRenderHook(function ($info, \Anakeen\Core\Internal\SmartElement $se) use ($abstractDataFunction, $field) {
                        $data = $abstractDataFunction($se);
                        $info["abstract"] = [
                            $field["field"] => $data
                        ];
                        return $info;
                    });
                } else {
                    if ($element->getAttribute($field["field"])) {
                        $df->setAttributes([$field["field"]]);
                    }
                }
            }
        }
        return $df->getData();
    }
}
