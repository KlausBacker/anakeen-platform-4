<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Ui;

class DocumentTemplateContext implements \ArrayAccess
{
    public $coucou = "HELLO";
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
    
    public function __construct(\Doc $doc)
    {
        $this->_document = $doc;
        //$la=$doc->getNormalAttributes();
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
    
    protected function _getProperties()
    {
        static $props = array();
        
        if ($props) {
            return $props;
        }
        
        if ($this->_document) {
            
            $propIds = array(
                "state",
                "fromname",
                "id",
                "postitid",
                "initid",
                "locked",
                "revision",
                "wid",
                "cvid",
                "profid",
                "fromid",
                "owner",
                "domainid"
            );
            foreach ($propIds as $propId) {
                $props[$propId] = $this->_document->$propId;
            }
            
            $props["icon"] = $this->_document->getIcon();
            $props["title"] = $this->_document->getTitle();
            $props["labelstate"] = $this->_document->state ? _($this->_document->state) : '';
            
            if ($props['id'] > 0) {
                $props["revdate"] = strftime("%Y-%m-%d %H:%M:%S", $this->_document->revdate);
                $props["readonly"] = ($this->_document->canEdit() != "");
                
                $props["lockdomainid"] = intval($this->_document->lockdomainid);
                // numeric values
                if ($props["postitid"]) $props["postitid"] = $this->_document->rawValueToArray($props["postitid"]);
                else $props["postitid"] = array();
                $props["id"] = intval($props["id"]);
                $props["initid"] = intval($props["initid"]);
                $props["locked"] = intval($props["locked"]);
                $props["revision"] = intval($props["revision"]);
                $props["wid"] = intval($props["wid"]);
                $props["cvid"] = intval($props["cvid"]);
                // $props["prelid"] = intval($props["prelid"]);
                $props["profid"] = intval($props["profid"]);
                //   $props["dprofid"] = intval($props["dprofid"]);
                $props["fromid"] = intval($props["fromid"]);
                // $props["allocated"] = intval($props["allocated"]);
                $props["owner"] = intval($props["owner"]);
                if ($props["domainid"]) $props["domainid"] = $this->_document->rawValueToArray($props["domainid"]);
                else $props["domainid"] = array();
            }
        }
        return $props;
    }
    
    protected function _getAttributes()
    {
        static $render = array();
        
        if ($this->_document->id == 0) {
            return array();
        }
        if ($render) {
            return $render[0]["attributes"];
        }
        $dl = new \DocumentList();
        $dl->addDocumentIdentifiers(array(
            $this->_document->id
        ) , false);
        
        $fmtCollection = new \FormatCollection($this->_document);
        $la = $this->_document->getNormalAttributes();
        foreach ($la as $aid => $attr) {
            if ($attr->type != "array") {
                $fmtCollection->addAttribute($aid);
            }
        }
        $render = $fmtCollection->render();
        return ($render[0]["attributes"]);
    }
    /**
     * Keys for mustache
     * @return array
     */
    public function document()
    {
        return array(
            "property" => $this->_getProperties() ,
            
            "attribute" => $this->_getAttributes()
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
    
    protected function _getDocumentStructure()
    {
        $la = $this->_document->getNormalAttributes();
        $t = array();
        foreach ($la as $oattr) {
            $parentAttr = $oattr->fieldSet;
            $parentIds = array();
            while ($parentAttr && $parentAttr->id != 'FIELD_HIDDENS') {
                $parentId = $parentAttr->id;
                $parentIds[] = $parentId;
                $parentAttr = $parentAttr->fieldSet;
            }
            $parentIds = array_reverse($parentIds);
            $previousId = null;
            foreach ($parentIds as $aid) {
                if ($previousId === null) {
                    if (!isset($t[$aid])) {
                        $t[$aid] = $this->getAttributeInfo($this->_document->getAttribute($aid));
                        $t[$aid]["content"] = array();
                    }
                    $target = & $t[$aid]["content"];
                } else {
                    if (!isset($t[$previousId]["content"][$aid])) {
                        
                        $t[$previousId]["content"][$aid] = $this->getAttributeInfo($this->_document->getAttribute($aid));
                        
                        $t[$previousId]["content"][$aid]["content"] = array();
                    }
                    $target = & $t[$previousId]["content"][$aid]["content"];
                }
                $previousId = $aid;
            }
            $target[$oattr->id] = $this->getAttributeInfo($oattr);
        }
        return $t;
    }
    
    protected function getAttributeInfo(\BasicAttribute $oa)
    {
        return array(
            "id" => $oa->id,
            "visibility" => $oa->mvisibility,
            "label" => $oa->getLabel() ,
            "type" => $oa->type,
            "multiple" => $oa->isMultiple(),
            "index" => $oa->ordered
        );
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
