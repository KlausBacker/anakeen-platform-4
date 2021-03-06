<?php


namespace Anakeen\Ui;

use Anakeen\Components\Grid\Operators;
use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentList;
use SmartStructure\Fields\Report;
use SmartStructure\Fields\Search;

class DataSource extends DocumentList
{
    /**
     * Filter data source informations
     * @var array
     */
    protected $filter = null;

    /**
     * Sort data source informations
     * @var array
     */
    protected $sort = null;

    /**
     * Page number
     * @var int
     */
    protected $page = null;
    /**
     * Page size
     * @var int
     */
    protected $pageSize = null;

    /**
     * Collection id
     * @var int
     */
    protected $smartElementId = null;

    /**
     * Collection element
     * @var SmartElement
     */
    protected $smartElement = null;

    /**
     * Parse url and query parameters
     * @param \Slim\Http\request $request - request object
     * @param                    $args    - url parameters
     */
    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $queryParams = $request->getQueryParams();
        //Pager infos
        if (isset($queryParams['page'])) {
            $this->page = $queryParams['page'];
        }
        if (isset($queryParams['pageSize'])) {
            $this->pageSize = $queryParams['pageSize'];
        }
        if (isset($queryParams['skip'])) {
            $this->offset = $queryParams['skip'];
        }
        if (isset($queryParams['take'])) {
            if (is_numeric($queryParams['take'])) {
                $this->slice = intval($queryParams['take']);
            } else {
                if ($queryParams['take'] == 'all') {
                    $this->slice = "ALL";
                }
            }
        }

        $this->computePage();

        // Sort infos
        if (isset($queryParams['sort'])) {
            $this->sort = $queryParams['sort'];
        }

        // filters infos
        if (isset($queryParams['filter'])) {
            $this->filter = $queryParams['filter'];
        }

        if (empty($queryParams['fields'])) {
            $this->returnFields = $this->defaultFields;
        } else {
            $this->returnFields = array_map("trim", explode(",", $queryParams['fields']));
        }

        $this->parseUrlArgs($args);
    }

    /**
     * Compute page and page size
     */
    protected function computePage()
    {
        if (!isset($this->pageSize)) {
            $this->pageSize = $this->slice;
        }
        // compute kendo page number (1 is first page)
        if (!isset($this->page) && is_numeric($this->slice)) {
            $this->page = intval($this->offset / $this->slice) + 1;
        }
    }

    protected function parseUrlArgs($urlArgs = array())
    {
        if (isset($urlArgs['collectionId'])) {
            $this->smartElementId = $urlArgs['collectionId'];
        }
    }

    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        if ($this->smartElementId === "-1") {
            $this->_searchDoc->fromid = -1;
            $this->smartElement = null;
        } else {
            $doc = SEManager::getDocument($this->smartElementId);
            if (!$doc) {
                $exception = new Exception('GRID0001', $this->smartElementId);
                $exception->setHttpStatus("404", "Smart Element not found");
                throw $exception;
            }
            $this->smartElement = $doc;
            switch ($this->smartElement->defDoctype) {
                case 'C':
                    $this->_searchDoc->fromid = $this->smartElement->id;
                    break;
                case 'D':
                    $this->_searchDoc->useCollection($this->smartElement->initid);
                    break;
                case 'S':
                    $famId = $this->smartElement->getRawValue(Search::se_famid);
                    if (empty($famId)) {
                        $this->_searchDoc->fromid = 0;
                    } else {
                        $this->_searchDoc->fromid = $famId;
                    }
                    $this->_searchDoc->useCollection($this->smartElement->initid);
                    break;
                default:
                    $exception = new Exception("GRID0002", $this->smartElementId);
                    $exception->setHttpStatus("400", "Smart Element is not a structure or collection");
                    throw $exception;
            }
        }
    }

    protected function prepareDocumentList()
    {
        parent::prepareDocumentList();
        $this->preparePaging();
        $this->prepareFiltering();
    }

    protected function preparePaging()
    {
        if (!empty($this->smartElement) && is_a($this->smartElement, \SmartStructure\Report::class)) {
            $repLimit = intval($this->smartElement->getRawValue(Report::rep_limit));
            if ($repLimit > 0) {
                $this->slice = $repLimit;
                $this->pageSize = $this->slice;
            }
        }
        if ($this->pageSize > 0) {
            $this->_searchDoc->setSlice($this->pageSize);
            $this->_searchDoc->setStart(($this->page - 1) * $this->pageSize);
        }
    }

    /**
     * interpretation of Kendo Filters
     */
    protected function prepareFiltering()
    {
        if (!empty($this->filter)) {
            // First need flat filters
            $flatFilters = static::getFlatLevelFilters($this->filter);

            foreach ($flatFilters as $filter) {
                $filterObject = Operators::getFilterObject($filter);
                if (!empty($filterObject)) {
                    $this->_searchDoc->addFilter($filterObject);
                }
            }
        }
    }

    public static function getFlatLevelFilters($filterArg)
    {
        $flatFilters = [];
        if (!empty($filterArg["filters"])) {
            $filters=$filterArg["filters"];
            foreach ($filters as $filter) {
                if (!empty($filter["field"])) {
                    $flatFilters[] = $filter;
                } elseif (!empty($filter["filters"])) {
                    $flatFilters = array_merge($flatFilters, $filter);
                }
            }
        }
        return $flatFilters;
    }

    protected function extractOrderBy()
    {
        if (!empty($this->smartElement)) {
            if (is_a($this->smartElement, \SmartStructure\Report::class)) {
                $sortOrderDir = $this->smartElement->getRawValue(Report::rep_ordersort, "asc");
                $sortField = $this->smartElement->getRawValue(Report::rep_idsort, "title");
                $orderBy = "$sortField:$sortOrderDir";
            }
            if (!empty($this->sort)) {
                $orderBy = implode(',', array_map(function ($item) {
                    return ($item['compare']?:$item['field']) . ":" . $item['dir'];
                }, $this->sort));
            }
            if (!empty($orderBy)) {
                switch ($this->smartElement->defDoctype) {
                    case "C":
                        return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy, $this->smartElement);
                    case "S":
                        $famId = $this->smartElement->getRawValue("se_famid");

                        return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy(
                            $orderBy,
                            SEManager::getFamily($famId)
                        );
                    default:
                        return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy);
                }
            }
        } else {
            if (!empty($this->sort)) {
                $orderBy = implode(',', array_map(function ($item) {
                    return $item['field'] . ":" . $item['dir'];
                }, $this->sort));
                if (!empty($orderBy)) {
                    return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy);
                }
            }
        }
        return parent::extractOrderBy();
    }

    protected function getData()
    {
        $documentList = $this->getDocumentList();
        $data = array(
            "requestParameters" => array(
                "filter" => $this->filter,
                "sort" => $this->sort
            )
        );
        $documentFormatter = $this->prepareDocumentFormatter($documentList);
        $docData = $documentFormatter->format();
        $data["smartElements"] = $docData;
        return $data;
    }
}
