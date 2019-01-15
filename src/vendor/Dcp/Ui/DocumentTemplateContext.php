<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Anakeen\Core\SEManager;

class DocumentTemplateContext extends \Anakeen\Core\Internal\I18nTemplateContext
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $_document = null;
    /**
     * @var string[] list of sub-template path
     */
    protected $templateSection = array();

    protected $docProperties = null;
    protected $docAttributes = null;
    /**
     * @var \Anakeen\Routes\Core\Lib\DocumentApiData
     */
    protected $_documentCrud = null;
    protected $_documentData = null;

    public function __construct(\Anakeen\Core\Internal\SmartElement $doc)
    {
        parent::__construct();
        $this->_document = $doc;
        if ($doc->id > 0) {
            SEManager::cache()->addDocument($doc);
        }
    }



    /**
     * Retrieve document data from CRUD API
     *
     * @param string $field
     * @param array  $subFields
     *
     * @return array|mixed|null
     */
    protected function _getDocumentData($field, $subFields = array())
    {

        if ($this->_documentCrud === null) {
            $this->_documentCrud = new \Anakeen\Routes\Core\Lib\DocumentApiData($this->_document);
            if (count($subFields) > 0) {
                $completeFields = array_map(function ($item) use ($field) {
                    return $field . '.' . $item;
                }, $subFields);
                $this->_documentCrud->setFields($completeFields);
            } else {
                $this->_documentCrud->setFields([$field]);
            }

            $this->_documentData = $this->_documentCrud->getDocumentData();
        }
        $fields = explode('.', $field);
        $data = $this->_documentData;
        // verify information access path exists
        foreach ($fields as $key) {
            $key = trim($key);
            $data = isset($data[$key]) ? $data[$key] : null;
        }

        if ($data === null) {
            $this->_documentCrud->setFields([$field]);
            $moreData = $this->_documentCrud->getInternal($this->_document);
            unset($moreData["document"]["uri"]);
            $this->_documentData = array_merge_recursive($this->_documentData, $moreData);

            $data = $this->_documentData;
            foreach ($fields as $key) {
                $key = trim($key);
                $data = isset($data[$key]) ? $data[$key] : null;
            }
        }
        return $data;
    }

    protected function _getProperties()
    {
        return $this->_getDocumentData("document.properties", array(
            "mdate",
            "icon",
            "revision",
            "family",
            "status"
        ));
    }

    protected function _getAttributes()
    {
        if ($this->_document->doctype === "C") {
            return array();
        }
        $attrData = $this->_getDocumentData("document.attributes");
        $ctxData = array();
        foreach ($attrData as $aid => $value) {
            $oa = $this->_document->getAttribute($aid);
            $ctxData[$aid] = array(
                "attributeValue" => $value,
                "label" => ($oa) ? $oa->getLabel() : ""
            );
        }
        return $ctxData;
    }

    /**
     * Keys for mustache
     * @return array
     */
    public function document()
    {
        return array(
            "properties" => $this->_getProperties(),
            "attributes" => $this->_getAttributes()
        );
    }

    /**
     *  Key for mustache
     * @return string
     */
    public function documentData()
    {
        $conf = array(
            "document" => array(
                "properties" => $this->_getProperties(),
                "attributes" => $this->_getAttributes()
            ),
            "family" => array(
                "structure" => $this->_getDocumentStructure()
            )
        );
        return JsonHandler::encodeForHTML($conf);
    }

    public function documentId()
    {

        return intval($this->_document->initid);
    }



    protected function _getDocumentStructure()
    {
        if ($this->_document->doctype === "C") {
            return null;
        }
        return $this->_getDocumentData("family.structure");
    }
}
