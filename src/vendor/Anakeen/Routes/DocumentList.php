<?php

namespace Dcp\Routes;

use Dcp\HttpApi\V1\Crud\DocumentFormatter;
use Dcp\HttpApi\V1\Crud\DocumentUtils;
use Dcp\HttpApi\V1\Crud\URLUtils;
use Dcp\Router\ApiV2Response;

class DocumentList
{
    const GET_PROPERTIES = "document.properties";
    const GET_PROPERTY = "document.properties.";
    const GET_ATTRIBUTES = "document.attributes";
    const GET_ATTRIBUTE = "document.attributes.";

    protected $defaultFields = null;
    protected $returnFields = null;
    protected $slice = 0;
    protected $offset = 0;
    protected $orderBy = "";
    /**
     * @var \Slim\Http\request
     */
    protected $request;
    /**
     * @var \SearchDoc
     */
    protected $_searchDoc = null;

    public function __construct()
    {
        $this->defaultFields = self::GET_PROPERTIES;
    }

    /**
     * Return all visible documents
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->request=$request;
        $documentList = $this->prepareDocumentList();
        $return = array(
            "requestParameters" => array(
                "slice" => $this->slice,
                "offset" => $this->offset,
                "length" => count($documentList),
                "orderBy" => $this->orderBy
            )
        );

        $return["uri"] = URLUtils::generateURL("documents/");
        $return["properties"] = $this->getCollectionProperties();
        $documentFormatter = $this->prepareDocumentFormatter($documentList);
        $data = $documentFormatter->format();
        $return["documents"] = $data;

        return  ApiV2Response::withData($response, $return);
    }

    /**
     * Get the restricted attributes
     *
     * @return array
     */
    protected function getAttributeFields()
    {
        $prefix = self::GET_ATTRIBUTE;
        $fields = $this->getFields();
        if ($this->hasFields(self::GET_ATTRIBUTE)) {
            return DocumentUtils::getAttributesFields(null, $prefix, $fields);
        }
        return array();
    }

    /**
     * Get the restrict fields value
     *
     * The restrict fields is used for restrict the return of the get request
     *
     * @return array|null
     */
    protected function getFields()
    {
        if ($this->returnFields === null) {
            $fields= $this->request->getQueryParam("fields");
            if (!$fields) {
                $fields = $this->defaultFields;
            }
            if ($fields) {
                $this->returnFields = array_map("trim", explode(",", $fields));
            } else {
                $this->returnFields = array();
            }
        }
        return $this->returnFields;
    }

    /**
     * Get the list of the properties required
     *
     * @return array
     */
    protected function _getPropertiesId()
    {
        $properties = array();
        $returnFields = $this->getFields();
        $subField = self::GET_PROPERTY;
        foreach ($returnFields as $currentField) {
            if (strpos($currentField, $subField) === 0) {
                $properties[] = substr($currentField, mb_strlen(self::GET_PROPERTY));
            }
        }
        return $properties;
    }

    /**
     * Check if the current restrict field exist
     *
     * @param string  $fieldId field
     * @param boolean $strict  strict test
     *
     * @return bool
     */
    protected function hasFields($fieldId, $strict = false)
    {
        $returnFields = $this->getFields();

        if (!$strict) {
            foreach ($returnFields as $aField) {
                if (strpos($aField, $fieldId) === 0) {
                    return true;
                }
            }
        } else {
            if (in_array($fieldId, $returnFields)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare the searchDoc
     * You can inherit of this function to make specialized collection (trash, search, etc...)
     */
    protected function prepareSearchDoc()
    {
        $this->_searchDoc = new \SearchDoc();
        $this->_searchDoc->setObjectReturn();
        $this->_searchDoc->excludeConfidential(true);
    }

    /**
     * Analyze the slice, offset and sortBy
     *
     * @return \DocumentList
     */
    public function prepareDocumentList()
    {
        $this->prepareSearchDoc();
        $slice=$this->request->getQueryParam("slice");

        $this->slice = !empty($slice)
            ?
            mb_strtolower($slice)
            :
            \Dcp\HttpApi\V1\Api\Router::getHttpApiParameter("COLLECTION_DEFAULT_SLICE");
        if ($this->slice !== "all") {
            $this->slice = intval($this->slice);
        }
        $this->_searchDoc->setSlice($this->slice);
        $this->offset = intval($this->request->getQueryParam("offset"));

        $this->_searchDoc->setStart($this->offset);
        $this->orderBy = $this->extractOrderBy();
        $this->_searchDoc->setOrder($this->orderBy);
        return $this->_searchDoc->getDocumentList();
    }

    protected function getCollectionProperties()
    {
        return array(
            "title" => ""
        );
    }

    /**
     * Extract orderBy
     *
     * @return string
     */
    protected function extractOrderBy()
    {
        $orderBy=$this->request->getQueryParam("orderby");
        if (!$orderBy) {
            $orderBy= "title:asc";
        }
        return DocumentUtils::extractOrderBy($orderBy);
    }

    /**
     * Initialize the document formatter
     * Extract the properties and attributes
     *
     * @param $documentList
     *
     * @return DocumentFormatter
     */
    protected function prepareDocumentFormatter($documentList)
    {
        $documentFormatter = new DocumentFormatter($documentList);
        if ($this->hasFields(self::GET_PROPERTIES, true) && !$this->hasFields(self::GET_PROPERTY)) {
            $documentFormatter->useDefaultProperties();
        } else {
            $documentFormatter->setProperties($this->_getPropertiesId(), $this->hasFields(self::GET_PROPERTIES, true));
        }
        $documentFormatter->setAttributes($this->getAttributeFields());
        return $documentFormatter;
    }

    /**
     * Initialize the default fields
     *
     * @param $fields
     *
     * @return $this
     */
    public function setDefaultFields($fields)
    {
        $this->returnFields = null;
        $this->defaultFields = $fields;
        return $this;
    }
}
