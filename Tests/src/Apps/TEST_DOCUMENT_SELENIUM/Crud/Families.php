<?php
namespace Dcp\Ui\Test\Crud;


class Families extends \Dcp\HttpApi\V1\Crud\DocumentCollection {

        /**
     * Read a ressource
     * @param string|int $resourceId Resource identifier
     * @return mixed
     */
    public function read($resourceId)
    {
        $documentList = $this->prepareDocumentList();
        $return = array(
            "requestParameters" => array(
                "slice" => $this->slice,
                "offset" => $this->offset,
                "length" => count($documentList) ,
                "orderBy" => $this->orderBy
            )
        );

        $return["uri"] = $this->generateURL("documents/");
        $return["properties"] = $this->getCollectionProperties();
        $documentFormatter = $this->prepareDocumentFormatter($documentList);
        $data = $documentFormatter->format();
        $return["documents"] = $data;

        return $return;
    }

    /**
     * Analyze the slice, offset and sortBy
     *
     * @return \DocumentList
     */
    public function prepareDocumentList()
    {
        $this->prepareSearchDoc();
        $this->slice = isset($this->contentParameters["slice"]) ? mb_strtolower($this->contentParameters["slice"]) : \Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("HTTPAPI_V1", "COLLECTION_DEFAULT_SLICE");
        if ($this->slice !== "all") {
            $this->slice = intval($this->slice);
        }
        $this->_searchDoc->setSlice($this->slice);
        $this->_searchDoc->fromid=-1;
        $this->offset = isset($this->contentParameters["offset"]) ? $this->contentParameters["offset"] : 0;
        $this->offset = intval($this->offset);
        $this->_searchDoc->setStart($this->offset);
        $this->orderBy = $this->extractOrderBy();
        $this->_searchDoc->setOrder($this->orderBy);

        $param=json_decode(\Anakeen\Core\Internal\ApplicationParameterManager::getParameterValue("TEST_DOCUMENT_SELENIUM", "TESTFAMILIES"));
        if ($param) {
            $this->_searchDoc->addFilter($this->_searchDoc->sqlcond($param,"name" ));
        }

        return $this->_searchDoc->getDocumentList();
    }

}