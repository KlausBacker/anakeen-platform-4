<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\DocumentList;
use Anakeen\Routes\Core\DocumentUtils;
use Dcp\Core\ContextManager;
use  Anakeen\Router\Exception;
use Dcp\Core\DocManager;

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

    protected $_filter = null;
    protected $page=1;
    protected $collectionId;
    /**
     * @var \DocFam
     */
    protected $family;


    /**
     * Read a ressource
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return mixed
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->page=intval($args["page"]);
        $this->collectionId=$args["collection"];
        $return = parent::__invoke($request, $response, $args);


        return $return;
    }

    protected function getData()
    {
        $data = parent::getData();

        $data["resultMax"] = $this->_searchDoc->onlyCount();
        $data['paginationState'] = $this->getPaginationState();
        $data['user'] = ["id"=>intval(ContextManager::getCurrentUser()->id), "fid"=>intval(ContextManager::getCurrentUser()->fid)];
        return $data;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        parent::prepareSearchDoc();
        $doc = DocManager::getDocument($this->collectionId);
        if (! $doc) {
            $exception = new Exception('DOCLIST0001', $this->collectionId);
            $exception->setHttpStatus("404", "Document not found");
            throw $exception;
        }
        switch ($doc->defDoctype) {
            case 'C':
                $this->family = $doc;

                $this->_searchDoc->fromid = $this->family->id;
                break;
            case 'F':
            case 'S':
                $this->collection = $doc;
                if (!$this->collection) {
                    $exception = new Exception("DOCLIST0002", $this->collectionId);
                    $exception->setHttpStatus("404", "Collection not found");
                    throw $exception;
                }
                 $this->_searchDoc->useCollection($this->collection->initid);
                break;
            default:
                $exception = new Exception("DOCLIST0003", $this->collectionId);
                $exception->setHttpStatus("400", "Document is not a family or collection");
                throw $exception;
        }
        $filter=$this->request->getQueryParam("filter");
        if (!empty($filter)) {
            $this->_filter = $filter;
            $this->_searchDoc->addFilter("title ~* '%s'", preg_quote($filter));
        }
    }

    protected function prepareDocumentList() {
        parent::prepareDocumentList();
        $this->_searchDoc->setStart(($this->page - 1) * $this->slice);
    }

    protected function extractOrderBy()
    {
        $orderBy=$this->request->getQueryParam("orderBy", "title:asc");
        if ($this->family) {
            return DocumentUtils::extractOrderBy($orderBy, $this->family);

        } else if ($this->collection) {
            $familyOfCollectionId = $this->collection->getRawValue("se_famid");
            if (isset($familyOfCollectionId)) {
                return DocumentUtils::extractOrderBy($orderBy, DocManager::getFamily($familyOfCollectionId));
            }
        }
        return parent::extractOrderBy();
    }

    protected function getPaginationState()
    {
        return ["page" => intval($this->page) , "slice" => intval($this->request->getQueryParam("slice")) , "total_entries" => $this->_searchDoc->onlyCount() ];
    }
}