<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

use Dcp\HttpApi\V1\DocManager;
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
     * @var \Dcp\Httpapi\V1\DocumentCrud
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
    
    protected static function _i18n($s)
    {
        if (!$s) return '';
        return _($s);
    }
    /**
     * Retrieve document data from CRUD API
     * @param string $field
     * @return array|mixed|null
     */
    protected function _getDocumentData($field)
    {
        
        if ($this->_documentCrud === null) {
            $this->_documentCrud = new \Dcp\HttpApi\V1\DocumentCrud();
            $this->_documentCrud->setDefaultFields($field);
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
                $data = $data[trim($key) ];
            }
        }
        return $data;
    }
    protected function _getProperties()
    {
        return $this->_getDocumentData("document.properties");
    }
    
    protected function _getAttributes()
    {
        return $this->_getDocumentData("document.attributes");
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
    
    public function userLocale()
    {
        $localeId = \ApplicationParameterManager::getScopedParameterValue("CORE_LANG");
        $config = getLocaleConfig($localeId);
        return $config["culture"];
    }
    
    protected function _getDocumentStructure()
    {
        
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
    public function offsetGet($offset)
    {
        $x = $this->keys[$offset];
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
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->keys[$offset] = $value;
    }
    /**
     *
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->keys[$offset]);
    }
}
