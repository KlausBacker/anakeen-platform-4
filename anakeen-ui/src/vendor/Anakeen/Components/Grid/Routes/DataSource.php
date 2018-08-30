<?php
/**
 * Created by PhpStorm.
 * User: aurelien
 * Date: 07/08/18
 * Time: 14:53
 */

namespace Anakeen\Components\Grid\Routes;


use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Routes\Core\DocumentList;

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
            $this->slice = $queryParams['take'];
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
        if (!isset($this->page)) {
            $this->page = intval($this->offset/$this->slice) + 1;
        }
    }

    /**
     * Co
     */
    protected function computeOrderBy() {

    }

    protected function parseUrlArgs($urlArgs = array()) {
        if (!empty($urlArgs['collectionId'])) {
            $this->smartElementId = $urlArgs['collectionId'];
        }
    }

    protected function prepareSearchDoc() {
        parent::prepareSearchDoc();
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
            case 'S':
                $this->_searchDoc->useCollection($this->smartElement->initid);
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
        $this->prepareSorting();
    }

    protected function preparePaging() {
        $this->_searchDoc->setSlice($this->pageSize);
        $this->_searchDoc->setStart(($this->page - 1) * $this->pageSize);
    }

    protected function prepareFiltering() {
        if (!empty($this->filter)) {
            foreach($this->filter['filters'] as $filter) {
                $operator = Operators::OPERATORS["ank:".$filter["operator"]]["operator"];
                error_log($operator);
                if (!empty($operator)) {
                    $this->_searchDoc->addFilter("%s " . $operator . " '%s'", $filter["field"], $filter["value"]);
                }
            }
        }
    }

    protected function prepareSorting() {

    }

    protected function getData() {
        $documentList = $this->getDocumentList();
        $data = array(
            "requestParameters" => array(
                "filter" => $this->filter,
                "pager" => array(
                    "page" => $this->page,
                    "skip" => $this->_searchDoc->start,
                    "take" => $this->_searchDoc->slice,
                    "pageSize" => $this->pageSize,
                    "total" => count($documentList),
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