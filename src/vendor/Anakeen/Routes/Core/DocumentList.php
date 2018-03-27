<?php

namespace Anakeen\Routes\Core;

use Anakeen\Core\Settings;
use Anakeen\Router\URLUtils;
use Anakeen\Router\ApiV2Response;

/**
 * Class DocumentList
 *
 * List all visible documents
 *
 * @note    Used by route : GET /api/v2/documents/
 * @package Anakeen\Routes\Core
 */
class DocumentList
{
    const GET_PROPERTIES = "document.properties";
    const GET_PROPERTY = "document.properties.";
    const GET_ATTRIBUTES = "document.attributes";
    const GET_ATTRIBUTE = "document.attributes.";

    protected $defaultFields = null;
    protected $returnFields = null;
    protected $slice = 10;
    protected $offset = 0;
    protected $orderBy = "title:asc";

    /**
     * @var \SearchDoc
     */
    protected $_searchDoc = null;

    public function __construct()
    {
        $this->defaultFields = [self::GET_PROPERTIES];
    }

    /**
     * Return all visible documents
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response
     *
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);

        $return = $this->getData();

        /**
         * @var \Slim\Http\response $response
         */
        return ApiV2Response::withData($response, $return);
    }


    protected function initParameters(\Slim\Http\request $request, $args)
    {
        if ($request->getQueryParam("offset") !== null) {
            $this->offset = intval($request->getQueryParam("offset"));
        }

        $slice = $request->getQueryParam("slice");

        if ($slice) {
            $this->slice = $slice;
            if ($this->slice !== "all") {
                $this->slice = intval($this->slice);
            }
        }

        $orderBy = $request->getQueryParam("orderby");
        if ($orderBy) {
            $this->orderBy = $orderBy;
        }

        $fields = $request->getQueryParam("fields");
        if (!$fields) {
            $this->returnFields = $this->defaultFields;
        } else {
            $this->returnFields = array_map("trim", explode(",", $fields));
        }
    }

    protected function getData()
    {
        $documentList = $this->getDocumentList();
        $data = array(
            "requestParameters" => array(
                "slice" => $this->_searchDoc->slice,
                "offset" => $this->_searchDoc->start,
                "length" => count($documentList),
                "orderBy" => $this->_searchDoc->orderby
            )
        );

        $data["uri"] = URLUtils::generateURL(Settings::ApiV2 . "documents/");
        $documentFormatter = $this->prepareDocumentFormatter($documentList);
        $docData = $documentFormatter->format();
        $data["documents"] = $docData;
        return $data;
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
            return \Anakeen\Routes\Core\Lib\DocumentUtils::getAttributesFields(null, $prefix, $fields);
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
     * @return void
     */
    protected function prepareDocumentList()
    {
        $this->prepareSearchDoc();
        $this->_searchDoc->setSlice($this->slice);


        $this->_searchDoc->setStart($this->offset);
        $this->_searchDoc->setOrder($this->extractOrderBy());
    }

    /**
     * Return Document list from searchDoc
     *
     * @return \DocumentList
     */
    protected function getDocumentList()
    {
        $this->prepareDocumentList();
        return $this->_searchDoc->getDocumentList();
    }


    /**
     * Extract orderBy
     *
     * @return string
     */
    protected function extractOrderBy()
    {
        return \Anakeen\Routes\Core\Lib\DocumentUtils::extractOrderBy($this->orderBy);
    }

    /**
     * Initialize the document formatter
     * Extract the properties and attributes
     *
     * @param $documentList
     *
     * @return CollectionDataFormatter
     */
    protected function prepareDocumentFormatter($documentList)
    {
        $documentFormatter = new CollectionDataFormatter($documentList);

        if ($this->hasFields(self::GET_PROPERTIES, true) && !$this->hasFields(self::GET_PROPERTY)) {
            $documentFormatter->useDefaultProperties();
        } else {
            $documentFormatter->setProperties($this->_getPropertiesId(), $this->hasFields(self::GET_PROPERTIES, true));
        }

        $documentFormatter->setAttributes($this->getAttributeFields());
        return $documentFormatter;
    }
}
