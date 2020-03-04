<?php

namespace Anakeen\Components\Grid;

use Anakeen\Components\Grid\Exceptions\Exception;
use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Search\SearchElements;
use SmartStructure\Fields\Search;

class SmartGridContentBuilder implements SmartGridBuilder
{
    /**
     * @var SearchElements
     */
    protected $searchElements = null;

    /**
     * @var GridDataFormatter $formatter
     */
    protected $formatter = null;

    protected $fields = [];
    protected $sort = [];

    protected $smartCollectionId = null;

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
     * @param mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     */
    public function __construct($collectionId = 0)
    {
        $this->setCollection($collectionId);
    }

    /**
     * Set the smart collection which the smart element Smart Element Grid is based on
     *
     * @param mixed $collectionId - Identifier of the collection (structure name/id, folder or report id),
     * it could be 0 for searching in all Smart Elements, or -1 for searching in all Smart Structures
     *
     * @return $this - the current instance
     */
    public function setCollection($collectionId)
    {
        if ($collectionId !== 0 && $collectionId !== -1) {
            $this->smartCollection = SEManager::getDocument($collectionId);
            if (!$this->smartCollection) {
                $exception = new Exception("GRID0001", $this->smartCollectionId);
                $exception->setHttpStatus("404", "Smart Element not found");
                throw $exception;
            }

            switch ($this->smartCollection->defDoctype) {
                case 'C':
                    $this->initSearch($collectionId);
                    break;
                case 'D':
                    $error = $this->smartCollection->control("open");
                    if ($error) {
                        $exception = new Exception("GRID0015", $this->smartCollectionId);
                        $exception->setHttpStatus("403", "Insufficient privileges");
                        throw $exception;
                    }
                    $this->initSearch();
                    $this->searchElements->useCollection($collectionId);
                    break;
                case 'S':
                    $error = $this->smartCollection->control("execute");
                    if ($error) {
                        $exception = new Exception("GRID0015", $this->smartCollectionId);
                        $exception->setHttpStatus("403", "Insufficient privileges");
                        throw $exception;
                    }
                    $fromId = $this->smartCollection->getRawValue(Search::se_famid, 0);
                    $this->initSearch($fromId);
                    $this->searchElements->useCollection($collectionId);
                    break;
            }
        } else {
            $this->initSearch($collectionId);
        }
        return $this;
    }

    /**
     * Add sort to content
     * @param $colId
     * @param $direction
     * @return $this
     */
    public function addSort($colId, $direction)
    {
        $this->sort[] = ["field" => $colId, "dir" => $direction];
        $order = implode(",", array_map(function ($sort) {
            return $sort["field"] . " " . $sort["dir"];
        }, $this->sort));
        $order .= ",id ASC";
        $this->searchElements->setOrder($order);
        return $this;
    }

    /**
     * Add filter to content.
     * @param $filter
     * @return $this
     * @throws \Anakeen\Search\Exception
     */
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

    /**
     * Add abstract column to content.
     * @param string $colId
     * @param array $options
     * @return $this
     */
    public function addAbstract($colId, $options = [])
    {
        $this->fields[] = array_merge([
            "field" => $colId,
            "property" => false,
            "abstract" => true
        ], $options);
        return $this;
    }

    /**
     * Add a smart field to content
     * @param string $fieldId
     * @param array $overload
     * @param string $structureName
     * @return $this|SmartGridBuilder
     */
    public function addField($fieldId, $overload = [], $structureName = "")
    {
        $this->fields[] = array_merge([
            "field" => $fieldId,
            "property" => false,
            "abstract" => false
        ], $overload);
        return $this;
    }

    /**
     * Add a property to content
     * @param string $propId
     * @param array $overload
     * @return $this|SmartGridBuilder
     */
    public function addProperty($propId, $overload = [])
    {
        $this->fields[] = [
            "field" => $propId,
            "property" => true,
            "abstract" => false
        ];
        return $this;
    }

    /**
     * Add un column to content. It can be a smart field, property or abstract
     * @param array $field
     * @return $this
     */
    public function addColumn($field)
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Set the columns to content. It can be a list smart field, property or abstract.
     * @param mixed $columns
     * @return $this
     */
    public function setColumns($columns)
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
        return $this;
    }

    /**
     * Set custom search
     *
     * @param SearchElements $search
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
        $return["requestParameters"] = $this->getRequestParameters();
        $this->prepareFormatter();
        $return["content"] = $this->formatter->format();
        if (ContextManager::getParameterValue("Ui", "MODE_DEBUG", false)) {
            $return["debug"] = $this->searchElements->getSearchInfo();
        }
        return $return;
    }

    /**
     * Get the internal search object
     * @return SearchElements
     */
    public function getSearch()
    {
        return $this->searchElements;
    }

    /**
     * Set the current content page
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        $this->setPager($this->page, $this->pageSize);
        return $this;
    }

    /**
     * Set the content page size
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        $this->setPager($this->page, $this->pageSize);
        return $this;
    }

    /**
     * Set the pageable Smart Element Grid configuration
     * @param $pageable
     * @return $this - the current instance
     */
    public function setPageable($pageable)
    {
        if (isset($pageable["pageSize"])) {
            $this->setPageSize($pageable["pageSize"]);
        }
        return $this;
    }

    /**
     * Set the client Smart Element Grid configuration in the builder
     * @param array $clientConfig
     * @return $this - the current instance
     */
    public function setClientConfig(array $clientConfig)
    {
        if (isset($clientConfig["pageable"])) {
            $this->setPageable($clientConfig["pageable"]);
        }
        if (isset($clientConfig["page"])) {
            $this->setPage($clientConfig["page"]);
        }
        if (isset($clientConfig["columns"])) {
            $this->setColumns($clientConfig["columns"]);
        }
        if (isset($clientConfig["filter"])) {
            $this->addFilter($clientConfig["filter"]);
        }
        if (isset($clientConfig["sort"])) {
            foreach ($clientConfig["sort"] as $sort) {
                $this->addSort($sort["field"], $sort["dir"]);
            }
        }
        return $this;
    }

    protected function prepareFormatter()
    {
        $this->formatter = new GridDataFormatter($this->searchElements);

        // Add property
        foreach ($this->fields as $field) {
            if (isset($field["field"])) {
                if (isset($field["property"]) && $field["property"]) {
                    $this->formatter->addProperty($field["field"]);
                } elseif (!isset($field["abstract"])) {
                    $this->formatter->setAttributes([$field["field"]]);
                }
            }
        }

        // Add abstract data
        $this->formatter->getFormatCollection()->setDocumentRenderHook(function ($info, \Anakeen\Core\Internal\SmartElement $se) {
            foreach ($this->fields as $field) {
                if (isset($field["field"])) {
                    if (isset($field["abstract"]) && $field["abstract"]) {
                        $abstractDataFunction = function ($se) {
                            return [
                                "value" => null,
                                "displayValue" => null
                            ];
                        };
                        if (isset($field["dataFunction"]) && is_callable($field["dataFunction"])) {
                            $abstractDataFunction = $field["dataFunction"];
                        }
                        $data = $abstractDataFunction($se);
                        $info["abstract"] = array_merge($info['abstract'] ?? [], [
                            $field["field"] => $data
                        ]);
                    } else {
                        if (empty($field["property"])) {
                            // Remove unexisting smart field from formatting
                            if (!$se->getAttribute($field['field'])) {
                                unset($info["attributes"][$field['field']]);
                            }
                        }
                    }
                }
            }
            return $info;
        });
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
}
