<?php
namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\Internal\SmartElement;
use Anakeen\SmartElementManager;
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
     * @param $args - url parameters
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
            } else if ($queryParams['take'] == 'all') {
                $this->slice = "ALL";
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

        if (!$queryParams['fields']) {
            $this->returnFields = $this->defaultFields;
        } else {
            $this->returnFields = array_map("trim", explode(",", $queryParams['fields']));
        }

        $this->parseUrlArgs($args);
    }

    /**
     * Compute page and page size
     */
    protected function computePage() {
        if (!isset($this->pageSize)) {
            $this->pageSize = $this->slice;
        }
        // compute kendo page number (1 is first page)
        if (!isset($this->page) && is_numeric($this->slice)) {
            $this->page = intval($this->offset/$this->slice) + 1;
        }
    }

    protected function parseUrlArgs($urlArgs = array()) {
        if (!empty($urlArgs['collectionId'])) {
            $this->smartElementId = $urlArgs['collectionId'];
        }
    }

    protected function prepareSearchDoc() {
        parent::prepareSearchDoc();
        $doc = SmartElementManager::getDocument($this->smartElementId);
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
                    $this->_searchDoc->useCollection($this->smartElement->initid);
                }
                break;
            default:
                $exception = new Exception("GRID0002", $this->smartElementId);
                $exception->setHttpStatus("400", "Smart Element is not a structure or collection");
                throw $exception;
        }
    }

    protected function prepareDocumentList()
    {
        parent::prepareDocumentList();
        $this->preparePaging();
        $this->prepareFiltering();
    }

    protected function preparePaging() {
        if (is_a($this->smartElement, \SmartStructure\Report::class)) {
            $repLimit = intval($this->smartElement->getRawValue(Report::rep_limit, $this->pageSize));
            $this->slice = $repLimit;
            $this->pageSize = $this->slice;
        }
        $this->_searchDoc->setSlice($this->pageSize);
        $this->_searchDoc->setStart(($this->page - 1) * $this->pageSize);
    }

    /**
     * interpretation of Kendo Filters
     */
    protected function prepareFiltering() {
        if (!empty($this->filter)) {
            foreach($this->filter['filters'] as $filter) {
                $query = Operators::OPERATORS["ank:".$filter["operator"]]["query"];
                $operands = Operators::OPERATORS["ank:".$filter["operator"]]["operands"];
                if (!empty($query)) {
                    if (!empty($operands)) {
                        $operandsValue = array_map(function ($item) use ($filter) {
                            return $filter[$item];
                        }, $operands);
                        $this->_searchDoc->addFilter($query, ...$operandsValue);
                    }
                }
            }
        }
    }

    protected function extractOrderBy()
    {
        if (is_a($this->smartElement, \SmartStructure\Report::class)) {
            $sortOrderDir = $this->smartElement->getRawValue(Report::rep_ordersort, "asc");
            $sortField = $this->smartElement->getRawValue(Report::rep_idsort, "title");
            $orderBy = "$sortField:$sortOrderDir";
        }
        if (!empty($this->sort)) {
            $orderBy = implode(',', array_map(function ($item) {
                return $item['field'] . ":" . $item['dir'];
            }, $this->sort));
        }
        if (!empty($orderBy)) {
            switch ($this->smartElement->defDoctype) {
                case "C":
                    return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy, $this->smartElement);
                case "D":
                    return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy);
                    break;
                case "S":
                    $famId = $this->smartElement->getRawValue("se_famid");
                    return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy, SmartElementManager::getFamily($famId));
            }

        }
        return parent::extractOrderBy();
    }

    protected function getData() {
        $documentList = $this->getDocumentList();
        $data = array(
            "requestParameters" => array(
                "filter" => $this->filter,
                "pager" => array(
                    "page" => intval($this->page),
                    "skip" => intval($this->_searchDoc->start),
                    "take" => intval($this->_searchDoc->slice),
                    "pageSize" => intval($this->pageSize),
                    "total" => intval($this->_searchDoc->onlyCount()),
                ),
                "sort" => $this->sort
            )
        );
        $documentFormatter = $this->prepareDocumentFormatter($documentList);
        $docData = $documentFormatter->format();
        $data["smartElements"] = $docData;
        return $data;
    }
}