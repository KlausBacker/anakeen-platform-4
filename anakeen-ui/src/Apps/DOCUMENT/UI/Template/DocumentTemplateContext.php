<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\HttpApi\V1\DocManager\DocManager;
class DocumentTemplateContext implements \ArrayAccess
{
    public $i18n;
    /**
     * @var \Doc
     */
    protected $_document = null;
    /**
     * @var string[] list of sub-template path
     */
    protected $templateSection = array();
    /**
     * Template extra keys
     * @var array
     */
    protected $keys = array();
    protected $docProperties = null;
    protected $docAttributes = null;
    /**
     * @var \Dcp\HttpApi\V1\Crud\Document
     */
    protected $_documentCrud = null;
    protected $_documentData = null;
    
    public function __construct(\Doc $doc)
    {
        $this->_document = $doc;
        if ($doc->id > 0) {
            DocManager::cache()->addDocument($doc);
        }
        $this->i18n = function ($s)
        {
            return self::_i18n($s);
        };
    }

    /**
     * Translate text using gettext context if exists
     * @param string $s text to translate
     *
     * @return string
     */
    protected static function _i18n($s)
    {
        if (!$s) return '';
        if (preg_match("/^([^(::)]+)::(.+)$/", $s, $reg)) {
            $i18n= ___($reg[2], $reg[1]);
            if ($i18n === $reg[1]) {
                $i18n = _($s);
                if ($i18n === $s) {
                    return $reg[1];
                }
            }
            return $i18n;
        }
        return _($s);
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
            $this->_documentCrud = new \Dcp\HttpApi\V1\Crud\Document();
            if (count($subFields) > 0) {
                
                $completeFields = array_map(function ($item) use ($field)
                {
                    return $field . '.' . $item;
                }
                , $subFields);
                $this->_documentCrud->setDefaultFields(implode(',', $completeFields));
            } else {
                $this->_documentCrud->setDefaultFields($field);
            }
            
            $this->_documentData = $this->_documentCrud->getInternal($this->_document);
        }
        $fields = explode('.', $field);
        $data = $this->_documentData;
        // verify information access path exists
        foreach ($fields as $key) {
            $key = trim($key);
            $data = isset($data[$key]) ? $data[$key] : null;
        }
        
        if ($data === null) {
            
            $this->_documentCrud->setDefaultFields($field);
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
            "revdate",
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
            "properties" => $this->_getProperties() ,
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
                "properties" => $this->_getProperties() ,
                "attributes" => $this->_getAttributes()
            ) ,
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
    public function userLocale()
    {
        $localeId = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $config = getLocaleConfig($localeId);
        return $config["culture"];
    }
    
    protected function _getDocumentStructure()
    {
        if ($this->_document->doctype === "C") {
            return null;
        }
        return $this->_getDocumentData("family.structure");
    }
    /**
     *
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->keys);
    }
    /**
     *
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function &offsetGet($offset)
    {
        $x = &$this->keys[$offset];
        if (is_callable($x)) {
            return call_user_func($x);
        }
        return $x;
    }
    /**
     *
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        $this->keys[$offset] = $value;
        return $this;
    }
    /**
     *
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return $this
     */
    public function offsetUnset($offset)
    {
        unset($this->keys[$offset]);
        return $this;
    }
}
