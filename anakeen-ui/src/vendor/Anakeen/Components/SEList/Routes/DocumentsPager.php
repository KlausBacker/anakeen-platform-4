<?php

namespace Anakeen\Components\SEList\Routes;

use Anakeen\Core\Internal\FormatCollection;
use Anakeen\Routes\Core\DocumentList;
use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;
use Anakeen\Core\SEManager;
use Anakeen\Routes\Core\Lib\DocumentDataFormatter;

/**
 * Class DocumentsList
 * @note Used by route : GET /api/v2/pager/{collection}/pages/{page}
 * @package Anakeen\Routes\DocumentsList
 */
class DocumentsPager extends DocumentList
{
    /**
     * @var \DocCollection definition of current collection
     */
    protected $collection = null;
    /**
     * @var string default value for order
     */
    protected $_defaultOrder = 'title asc';

    protected $filter = null;
    protected $page = 1;
    protected $collectionId;


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);

        $this->page = intval($args["page"]);
        $this->collectionId = $args["collection"];
        $this->filter = $request->getQueryParam("filter");

        $this->orderBy = $request->getQueryParam("orderBy", "title:asc");
        $this->slice = intval($request->getQueryParam("slice"));
    }

    protected function getData()
    {
        $data = parent::getData();
        $df = new DocumentDataFormatter($this->collection);
        $data["collection"] = $df->format()[0];
        $data["resultMax"] = $this->_searchDoc->onlyCount();
        $data['paginationState'] = $this->getPaginationState();
        $data['user'] = ["id" => intval(ContextManager::getCurrentUser()->id), "fid" => intval(ContextManager::getCurrentUser()->fid)];
        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $doc = SEManager::getDocument($this->collectionId);
        if (!$doc) {
            $exception = new Exception('DOCLIST0001', $this->collectionId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }

        $this->collection = $doc;
        switch ($this->collection->defDoctype) {
            case 'C':
                $this->_searchDoc->fromid = $this->collection->id;
                break;
            case 'F':
            case 'S':
                $this->_searchDoc->useCollection($this->collection->initid);
                break;
            default:
                $exception = new Exception("DOCLIST0003", $this->collectionId);
                $exception->setHttpStatus("400", "Document is not a family or collection");
                throw $exception;
        }
        if (!empty($this->filter)) {
            $this->_searchDoc->addFilter("title ~* '%s'", preg_quote($this->filter));
        }
    }

    protected function prepareDocumentList()
    {
        parent::prepareDocumentList();
        $this->_searchDoc->setStart(($this->page - 1) * $this->slice);
    }

    protected function extractOrderBy()
    {
        $orderBy = $this->orderBy;
        if ($this->collection->defDoctype === 'C') {
            return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy, $this->collection);
        } elseif ($this->collection->defDoctype === 'F' || $this->collection->defDoctype === 'S') {
            $familyOfCollectionId = $this->collection->getRawValue("se_famid");
            if (isset($familyOfCollectionId)) {
                return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($orderBy, SEManager::getFamily($familyOfCollectionId));
            }
        }
        return parent::extractOrderBy();
    }

    protected function getPaginationState()
    {
        return ["page" => intval($this->page), "slice" => $this->slice, "total_entries" => $this->_searchDoc->onlyCount()];
    }
}
