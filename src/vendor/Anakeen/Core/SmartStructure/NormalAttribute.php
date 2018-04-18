<?php

/**
 * NormalAttribute Class
 * Non structural attribute (all attribute except frame and tab)
 *
 * @author Anakeen
 *
 */
namespace Anakeen\Core\SmartStructure;

class NormalAttribute extends BasicAttribute
{
    const _cEnum = "_CACHE_ENUM";
    const _cEnumLabel = "_CACHE_ENUMLABEL";
    const _cParent = "_CACHE_PARENT";
    /**
     * @var bool
     */
    public $needed; // Y / N
    public $format; // C format
    public $eformat; // format for edition : list,vcheck,hcheck
    public $repeat; // true if is a repeatable attribute
    public $isNormal = true;
    /**
     * @var bool
     */
    public $isInTitle;
    /**
     * @var bool
     */
    public $isInAbstract;
    public $link; // hypertext link
    public $phpfile;
    public $phpfunc;
    public $elink; // extra link
    public $phpconstraint; // special constraint set
    
    /**
     * @var bool special use for application interface
     */
    public $isAlone = false;
    /**
     * @var $enum array use for enum attributes
     */
    public $enum;
    /**
     * @var $enumlabel array use for enum attributes
     */
    public $enumlabel;
    /**
     * Array of separator by level of multiplicity for textual export
     * @var array
     */
    protected $textualValueMultipleSeparator = array(
        0 => "\n",
        1 => ", "
    );
    /**
     * @var array
     */
    private static $_cache = array();
    protected $originalPhpfile;
    protected $originalPhpfunc;
    /**
     * Normal Attribute constructor : non structural attribute
     *
     * @param int $id id of the attribute
     * @param int $docid id of the family
     * @param string $label default translate key
     * @param string $type kind of attribute
     * @param string $format format option
     * @param string $repeat is repeteable attr
     * @param int $order display order
     * @param string $link link option
     * @param string $visibility visibility option
     * @param bool $needed is mandotary attribute
     * @param bool $isInTitle is used to compute title
     * @param bool $isInAbstract is used in abstract view
     * @param \Anakeen\Core\SmartStructure\FieldSetAttribute &$fieldSet parent attribute
     * @param string $phpfile php file used with the phpfunc
     * @param string $phpfunc helpers function
     * @param string $elink eling option
     * @param string $phpconstraint class php function
     * @param string $usefor Attribute or Parameter
     * @param string $eformat eformat option
     * @param string $options option string
     * @param string $docname
     */
    public function __construct($id, $docid, $label, $type, $format, $repeat, $order, $link, $visibility, $needed, $isInTitle, $isInAbstract, &$fieldSet, $phpfile, $phpfunc, $elink, $phpconstraint = "", $usefor = "", $eformat = "", $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->type = $type;
        $this->format = $format;
        $this->eformat = $eformat;
        $this->ordered = $order;
        $this->link = $link;
        $this->visibility = $visibility;
        $this->needed = $needed;
        $this->isInTitle = $isInTitle;
        $this->isInAbstract = $isInAbstract;
        $this->fieldSet = & $fieldSet;
        $this->phpfile = $phpfile;
        $this->phpfunc = $phpfunc;
        $this->elink = $elink;
        $this->phpconstraint = $phpconstraint;
        $this->usefor = $usefor;
        $this->repeat = $repeat;
        $this->options = $options;
        $this->docname = $docname;
    }
    /**
     * temporary change need
     * @param bool $need true means needed, false not needed
     * @return void
     */
    public function setNeeded($need)
    {
        $this->needed = $need;
    }
    /**
     * Parse htmltext and replace id by logicalname for links
     *
     * @param string $value Formated value of attribute
     * @return string Value transformed
     */
    public function prepareHtmltextForExport($value)
    {
        if ($this->type == "htmltext") {
            $value = preg_replace_callback('/(data-initid=")([0-9]+)/', function ($matches) {
                $name = \Anakeen\Core\DocManager::getNameFromId($matches[2]);
                return $matches[1] . ($name ? $name : $matches[2]);
            }, $value);
        }
        return $value;
    }
    /**
     * Generate the xml schema fragment
     *
     * @param array $la array of DocAttribute
     *
     * @return string
     */
    public function getXmlSchema($la)
    {
        switch ($this->type) {
            case 'text':
                return $this->text_getXmlSchema($la);
            case 'longtext':
            case 'htmltext':
                return $this->longtext_getXmlSchema($la);
            case 'int':
            case 'integer':
                return $this->int_getXmlSchema($la);
            case 'float':
            case 'money':
                return $this->float_getXmlSchema($la);
            case 'image':
            case 'file':
                return $this->file_getXmlSchema($la);
            case 'enum':
                return $this->enum_getXmlSchema($la);
            case 'thesaurus':
            case 'docid':
            case 'account':
                return $this->docid_getXmlSchema($la);
            case 'date':
                return $this->date_getXmlSchema($la);
            case 'timestamp':
                return $this->timestamp_getXmlSchema($la);
            case 'time':
                return $this->time_getXmlSchema($la);
            case 'array':
                return $this->array_getXmlSchema($la);
            case 'color':
                return $this->color_getXmlSchema($la);
            default:
                return sprintf("<!-- no Schema %s (type %s)-->", $this->id, $this->type);
        }
    }
    /**
     * Generate XML schema layout
     *
     * @param \Layout $play
     */
    public function common_getXmlSchema(&$play)
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "infoattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $lay->set("label", $this->encodeXml($this->labelText));
        $lay->set("type", $this->type);
        $lay->set("visibility", $this->visibility);
        $lay->set("isTitle", $this->isInTitle);
        $lay->set("phpfile", $this->phpfile);
        $lay->set("phpfunc", $this->phpfunc);
        
        if (($this->type == "enum") && (!$this->phpfile) || ($this->phpfile == "-")) {
            $lay->set("phpfile", false);
            $lay->set("phpfunc", false);
        }
        $lay->set("computed", ((!$this->phpfile) && (substr($this->phpfunc, 0, 2) == "::")));
        $lay->set("link", $this->encodeXml($this->link));
        $lay->set("elink", $this->encodeXml($this->elink));
        $lay->set("default", false); // TODO : need detect default value
        $lay->set("constraint", $this->phpconstraint);
        $tops = $this->getOptions();
        $t = array();
        foreach ($tops as $k => $v) {
            if ($k) {
                $t[] = array(
                "key" => $k,
                "val" => $this->encodeXml($v)
            );
            }
        }
        $lay->setBlockData("options", $t);
        
        $play->set("minOccurs", $this->needed ? "1" : "0");
        $play->set("isnillable", $this->needed ? "false" : "true");
        $play->set("maxOccurs", (($this->getOption('multiple') == 'yes') ? "unbounded" : "1"));
        $play->set("aname", $this->id);
        $play->set("appinfos", $lay->gen());
    }

    /**
     * custom textual XML schema
     *
     * @return string
     */
    public function text_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "textattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        
        $lay->set("maxlength", false);
        $lay->set("pattern", false);
        return $lay->gen();
    }
    /**
     * enum XML schema
     *
     * @return string
     */
    public function enum_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "enumattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        
        $la = $this->getEnum();
        $te = array();
        foreach ($la as $k => $v) {
            $te[] = array(
                "key" => $k,
                "val" => $this->encodeXml($v)
            );
        }
        $lay->setBlockData("enums", $te);
        return $lay->gen();
    }
    /**
     * docid XML schema
     *
     * @return string
     */
    public function docid_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "docidattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        
        $lay->set("famid", $this->format);
        return $lay->gen();
    }
    /**
     * date XML schema
     *
     * @return string
     */
    public function date_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "dateattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * timeStamp XML schema
     *
     * @return string
     */
    public function timestamp_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "timestampattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Color XML schema
     *
     * @return string
     */
    public function color_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "colorattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * int XML schema
     *
     * @return string
     */
    public function int_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "intattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * longText XML schema
     *
     * @return string
     */
    public function longtext_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "longtextattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Float XML schema
     *
     * @return string
     */
    public function float_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "floatattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Time XML schema
     *
     * @return string
     */
    public function time_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "timeattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * File XML schema
     *
     * @return string
     */
    public function file_getXmlSchema()
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "fileattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        return $lay->gen();
    }
    /**
     * Array XML schema
     * @param BasicAttribute[] &$la
     *
     * @return string
     */
    public function array_getXmlSchema(&$la)
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "arrayattribute_schema.xml"));
        $this->common_getXmlSchema($lay);
        $lay->set("minOccurs", "0");
        $lay->set("maxOccurs", "unbounded");
        $tax = array();
        foreach ($la as $k => $v) {
            if ($v->fieldSet && $v->fieldSet->id == $this->id) {
                $tax[] = array(
                    "axs" => $v->getXmlSchema($la)
                );
            }
        }
        $lay->setBlockData("ATTR", $tax);
        return $lay->gen();
    }
    /**
     * Get the textual value of an attribute
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc current Doc
     * @param int $index index if multiple
     * @param array $configuration value config array :
     * dateFormat => 'US' 'ISO',
     * decimalSeparator => '.',
     * longtextMultipleBrToCr => ' '
     * multipleSeparator => array(0 => 'arrayLine', 1 => 'multiple')
     *
     * (defaultValue : dateFormat : 'US', decimalSeparator : '.', multiple => array(0 => "\n", 1 => ", "))
     *
     * @return string
     */
    public function getTextualValue(\Anakeen\Core\Internal\SmartElement $doc, $index = - 1, array $configuration = array())
    {
        $decimalSeparator = isset($configuration['decimalSeparator']) ? $configuration['decimalSeparator'] : '.';
        
        if (in_array($this->type, array(
            "int",
            "double",
            "money"
        ))) {
            return $this->getNumberValue($doc, $index, $decimalSeparator);
        }
        $value = $doc->getRawValue($this->id);
        $fc = new \FormatCollection();
        $stripHtmlTags = isset($configuration['stripHtmlTags']) ? $configuration['stripHtmlTags'] : false;
        $fc->stripHtmlTags($stripHtmlTags);
        
        $fc->setDecimalSeparator($decimalSeparator);
        $fc->setDateStyle(\DateAttributeValue::defaultStyle);
        $dateFormat = isset($configuration['dateFormat']) ? $configuration['dateFormat'] : 'US';
        
        if ($dateFormat == 'US') {
            $fc->setDateStyle(\DateAttributeValue::isoWTStyle);
        } elseif ($dateFormat == "ISO") {
            $fc->setDateStyle(\DateAttributeValue::isoStyle);
        } elseif ($dateFormat == 'FR') {
            $fc->setDateStyle(\DateAttributeValue::frenchStyle);
        } else {
            $fc->setDateStyle(\DateAttributeValue::defaultStyle);
        }
        if (isset($configuration['longtextMultipleBrToCr'])) {
            $fc->setLongtextMultipleBrToCr($configuration['longtextMultipleBrToCr']);
        } else {
            $fc->setLongtextMultipleBrToCr(" "); // long text are in a single line
        }
        $info = $fc->getInfo($this, $value, $doc);
        if (empty($info)) {
            return '';
        }
        return \FormatCollection::getDisplayValue($info, $this, $index, $configuration);
    }
    
    public function getNumberValue(\Anakeen\Core\Internal\SmartElement $doc, $index = - 1, $decimalSeparator = ".")
    {
        if ($index >= 0) {
            $numberValue = $doc->getMultipleRawValues($this->id, "", $index);
            if ($this->format) {
                $numberValue = sprintf($this->format, $numberValue);
            }
        } elseif ($this->isMultiple() && $this->format) {
            $cellValues = $doc->getMultipleRawValues($this->id);
            foreach ($cellValues as & $cell) {
                $cell = sprintf($this->format, $cell);
            }
            $numberValue = implode("\n", $cellValues);
        } else {
            $numberValue = $doc->getRawValue($this->id);
            if ($this->format) {
                $numberValue = sprintf($this->format, $numberValue);
            }
        }
        
        if (!empty($decimalSeparator)) {
            $numberValue = str_replace(".", $decimalSeparator, $numberValue);
        }
        return $numberValue;
    }
    /**
     * to see if an attribute is n item of an array
     *
     * @return boolean
     */
    public function inArray()
    {
        return ($this->fieldSet && $this->fieldSet->type === "array");
    }
    /**
     * Return array of enumeration definition
     * the array's keys are the enum key and the values are the labels
     *
     * @param bool $returnDisabled if false disabled enum are not returned
     * @return array
     */
    public function getEnum($returnDisabled = true)
    {
        $cached = self::_cacheFetch(self::_cEnum, array(
            $this->docid,
            $this->id
        ), null, $returnDisabled);
        if ($cached !== null) {
            return $cached;
        }
        
        if (($this->type == "enum") || ($this->type == "enumlist")) {
            // set the enum array
            $this->enum = array();
            $this->enumlabel = array();
            $br = $this->docname . '#' . $this->id . '#'; // id i18n prefix
            if ($this->originalPhpfile && $this->originalPhpfunc) {
                $this->phpfile = $this->originalPhpfile;
                $this->phpfunc = $this->originalPhpfunc;
            }
            if (($this->phpfile != "") && ($this->phpfile != "-")) {
                // for dynamic  specification of kind attributes
                if (!include_once("EXTERNALS/$this->phpfile")) {
                    /**
                     * @var \Anakeen\Core\Internal\Action $action
                     */
                    global $action;
                    $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $this->phpfile));
                }
                if (preg_match('/(.*)\((.*)\)/', $this->phpfunc, $reg)) {
                    $args = explode(",", $reg[2]);
                    if (preg_match('/linkenum\((.*),(.*)\)/', $this->phpfunc, $dreg)) {
                        $br = $dreg[1] . '#' . strtolower($dreg[2]) . '#';
                    }
                    if (function_exists($reg[1])) {
                        $this->originalPhpfile = $this->phpfile;
                        $this->originalPhpfunc = $this->phpfunc;
                        $this->phpfile = "";
                        $this->phpfunc = call_user_func_array($reg[1], $args);
                        
                        \EnumAttributeTools::flatEnumNotationToEnumArray($this->phpfunc, $this->enum, $this->enumlabel, $br);
                    } else {
                        \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("function [%s] not exists"), $this->phpfunc));
                        $this->phpfunc = "";
                    }
                } else {
                    \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("invalid syntax for [%s] for enum attribute [%s]"), $this->phpfunc, $this->id));
                }
                self::_cacheStore(self::_cEnum, array(
                    $this->docid,
                    $this->id
                ), $this->enum);
                self::_cacheStore(self::_cEnumLabel, array(
                    $this->docid,
                    $this->id
                ), $this->enumlabel);
            } else {
                // static enum
                $famId = $this->_getRecursiveParentFamHavingAttribute($this->docid, $this->id);
                
                $cached = self::_cacheFetch(self::_cEnum, array(
                    $famId,
                    $this->id
                ), null, $returnDisabled);
                if ($cached !== null) {
                    return $cached;
                }
                
                $sql = sprintf("select * from docenum where famid=%d and attrid='%s' order by eorder", $famId, pg_escape_string($this->id));
                
                simpleQuery('', $sql, $enums);
                
                foreach ($enums as $k => $item) {
                    $enums[$k]["keyPath"] = str_replace('.', '\\.', $item["key"]);
                }
                foreach ($enums as $item) {
                    $enumKey = $item["key"];
                    $enumPath = $item["keyPath"];
                    $translatedEnumValue = _($br . $enumKey);
                    if ($translatedEnumValue != $br . $enumKey) {
                        $enumLabel = $translatedEnumValue;
                    } else {
                        $enumLabel = $item["label"];
                    }
                    if ($item["parentkey"] !== null) {
                        $this->enum[$this->getCompleteEnumKey($enumKey, $enums) ] = $enumLabel;
                        $enumCompleteLabel = $this->getCompleteEnumlabel($enumKey, $enums, $br);
                        $this->enumlabel[$enumKey] = $enumCompleteLabel;
                    } else {
                        $this->enum[$enumPath] = $enumLabel;
                        $this->enumlabel[$enumKey] = $enumLabel;
                    }
                }
                self::_cacheStore(self::_cEnum, array(
                    $famId,
                    $this->id
                ), $this->enum);
                self::_cacheStore(self::_cEnumLabel, array(
                    $famId,
                    $this->id
                ), $this->enumlabel);
            }
        }
        if (!$returnDisabled) {
            return self::_cacheFetch(self::_cEnum, array(
                $this->docid,
                $this->id
            ), null, $returnDisabled);
        }
        return $this->enum;
    }
    
    private function getCompleteEnumKey($key, array & $enums)
    {
        foreach ($enums as $item) {
            if ($item["key"] === $key) {
                if ($item["parentkey"] !== null) {
                    return sprintf("%s.%s", $this->getCompleteEnumKey($item["parentkey"], $enums), $item["keyPath"]);
                } else {
                    return $item["keyPath"];
                }
            }
        }
        return '';
    }
    private function getCompleteEnumLabel($key, array & $enums, $prefix)
    {
        foreach ($enums as $item) {
            if ($item["key"] === $key) {
                $translatedEnumValue = _($prefix . $key);
                if ($translatedEnumValue != $prefix . $key) {
                    $label = $translatedEnumValue;
                } else {
                    $label = $item["label"];
                }
                if ($item["parentkey"] !== null) {
                    return sprintf("%s/%s", $this->getCompleteEnumLabel($item["parentkey"], $enums, $prefix), $label);
                } else {
                    return $label;
                }
            }
        }
        return '';
    }
    /**
     * reset Enum cache
     */
    public static function resetEnum()
    {
        self::_cacheFlush(self::_cEnum);
        self::_cacheFlush(self::_cEnumLabel);
        self::_cacheFlush(self::_cParent);
    }
    /**
     * return array of enumeration definition
     * the array'skeys are the enum single key and the values are the complete labels
     *
     * @param string $enumid the key of enumerate (if no parameter all labels are returned
     * @param bool $returnDisabled if false disabled enum are not returned
     * @return array|string|null
     */
    public function getEnumLabel($enumid = null, $returnDisabled = true)
    {
        $implode = false;
        $this->getEnum($returnDisabled);
        
        $cached = self::_cacheFetch(self::_cEnumLabel, array(
            $this->docid,
            $this->id
        ), null, $returnDisabled);
        if ($cached === null) {
            $famId = $this->_getRecursiveParentFamHavingAttribute($this->docid, $this->id);
            if ($famId !== $this->docid) {
                $cached = self::_cacheFetch(self::_cEnumLabel, array(
                    $famId,
                    $this->id
                ), null, $returnDisabled);
            }
        }
        if ($cached !== null) {
            if ($enumid === null) {
                return $cached;
            }
            if (strstr($enumid, "\n")) {
                $enumid = explode("\n", $enumid);
                $implode = true;
            }
            if (is_array($enumid)) {
                $tv = array();
                foreach ($enumid as $v) {
                    $tv[] = (isset($cached[$v])) ? $cached[$v] : $v;
                }
                if ($implode) {
                    return implode("\n", $tv);
                }
                return $tv;
            } else {
                return (array_key_exists($enumid, $cached)) ? $cached[$enumid] : $enumid;
            }
        }
        
        return null;
    }
    /**
     * add new \item in enum list items
     *
     * @param string $dbaccess dbaccess string
     * @param string $key database key
     * @param string $label human label
     *
     * @return string error message (empty means ok)
     */
    public function addEnum($dbaccess, $key, $label)
    {
        $err = '';
        if ($key == "") {
            return "";
        }
        
        $famId = $this->docid;
        $attrId = $this->id;
        
        $a = new \DocAttr($dbaccess, array(
            $famId,
            $attrId
        ));
        if (!$a->isAffected()) {
            /* Search attribute in parents */
            $a = $this->_getDocAttrFromParents($dbaccess, $famId, $attrId);
            if ($a === false) {
                $err = sprintf(_("unknow attribute %s (family %s)"), $attrId, $famId);
                return $err;
            }
        }
        if ($a->isAffected()) {
            $famId = $a->docid;
            $oe = new \DocEnum($dbaccess, array(
                $famId,
                $attrId,
                $key
            ));
            $this->getEnum();
            
            $key = str_replace(array(
                '|'
            ), array(
                '_'
            ), $key);
            $label = str_replace(array(
                '|'
            ), array(
                '_'
            ), $label);
            if (!$oe->isAffected()) {
                $oe->attrid = $attrId;
                $oe->famid = $famId;
                $oe->key = $key;
                $oe->label = $label;
                /* Store enum in database */
                $err = $oe->add();
                if ($err == '') {
                    /* Update cache */
                    $cachedEnum = self::_cacheFetch(self::_cEnum, array(
                        $famId,
                        $this->id
                    ), array());
                    $cachedEnumLabel = self::_cacheFetch(self::_cEnumLabel, array(
                        $famId,
                        $this->id
                    ), array());
                    $cachedEnum[$key] = $label;
                    $cachedEnumLabel[$key] = $label;
                    self::_cacheStore(self::_cEnum, array(
                        $famId,
                        $this->id
                    ), $cachedEnum);
                    self::_cacheStore(self::_cEnumLabel, array(
                        $famId,
                        $this->id
                    ), $cachedEnumLabel);
                }
            }
        } else {
            $err = sprintf(_("unknow attribute %s (family %s)"), $attrId, $famId);
        }
        return $err;
    }
    private function _getRecursiveParentFamHavingAttribute($famId, $attrId)
    {
        $cached = self::_cacheFetch(self::_cParent, array(
            $famId,
            $attrId
        ));
        if ($cached !== null) {
            return $cached;
        }
        $sql = <<<'SQL'
WITH RECURSIVE parent_attr(fromid, docid, id) AS (
    SELECT
        docfam.fromid,
        docattr.docid,
        docattr.id
    FROM
        docattr,
        docfam
    WHERE
        docattr.docid = docfam.id
        AND
        docattr.docid = %d

    UNION

    SELECT
        docfam.fromid,
        docattr.docid,
        docattr.id
    FROM
        docattr,
        docfam,
        parent_attr
    WHERE
        docattr.docid = parent_attr.fromid
        AND
        parent_attr.fromid = docfam.id
)
SELECT docid FROM parent_attr WHERE id = '%s' LIMIT 1;
SQL;
        $sql = sprintf($sql, pg_escape_string($famId), pg_escape_string($attrId));
        $parentFamId = false;
        simpleQuery('', $sql, $parentFamId, true, true);
        if ($parentFamId !== false) {
            self::_cacheStore(self::_cParent, array(
                $famId,
                $attrId
            ), $parentFamId);
        }
        return $parentFamId;
    }
    private function _getDocAttrFromParents($dbaccess, $famId, $attrId)
    {
        $parentFamId = $this->_getRecursiveParentFamHavingAttribute($famId, $attrId);
        if ($parentFamId === false) {
            return false;
        }
        $a = new \DocAttr($dbaccess, $parentFamId, $attrId);
        return $a;
    }
    /**
     * Test if an enum key exists
     *
     * @param string $key enumKey
     * @param bool $completeKey if true test complete key with path else without path
     * @return bool
     */
    public function existEnum($key, $completeKey = true)
    {
        if ($key == "") {
            return false;
        }
        
        if ($completeKey) {
            $enumKeys = $this->getEnum();
        } else {
            $enumKeys = $this->getEnumLabel();
        }
        return isset($enumKeys[$key]);
    }
    /**
     * Construct a string key
     *
     * @param mixed $k key
     * @return string
     */
    private static function _cacheKey($k)
    {
        if (is_scalar($k)) {
            return $k;
        } elseif (is_array($k)) {
            return implode(':', $k);
        }
        return serialize($k);
    }
    /**
     * Check if an entry exists for the given key
     *
     * @param string $cacheId cache Id
     * @param string $k key
     * @return bool true if it exists, false if it does not exists
     */
    private static function _cacheExists($cacheId, $k)
    {
        $k = self::_cacheKey($k);
        return isset(self::$_cache[$cacheId][$k]);
    }
    /**
     * Add (or update) a key/value
     *
     * @param string $cacheId cache Id
     * @param string|string[] $k key
     * @param mixed $v value
     * @return bool true on success, false on failure
     */
    private static function _cacheStore($cacheId, $k, $v)
    {
        $k = self::_cacheKey($k);
        self::$_cache[$cacheId][$k] = $v;
        return true;
    }
    /**
     * Fetch the key's value
     *
     * @param string $cacheId cache Id
     * @param string|string[] $k key
     * @param mixed $onCacheMiss value returned on cache miss (default is null)
     * @param bool $returnDisabled if false unreturn disabled enums
     * @return null|mixed null on failure, mixed value on success
     */
    private static function _cacheFetch($cacheId, $k, $onCacheMiss = null, $returnDisabled = true)
    {
        if (self::_cacheExists($cacheId, $k)) {
            $ks = self::_cacheKey($k);
            if (!$returnDisabled) {
                $famId = $k[0];
                $attrid = $k[1];
                $disabledKeys = \DocEnum::getDisabledKeys($famId, $attrid);
                if (!empty($disabledKeys)) {
                    $cached = self::$_cache[$cacheId][$ks];
                    foreach ($disabledKeys as $dKey) {
                        unset($cached[$dKey]);
                    }
                    return $cached;
                }
            }
            
            return self::$_cache[$cacheId][$ks];
        }
        return $onCacheMiss;
    }

    /**
     * Flush the cache contents
     *
     * @param string|null $cacheId cache Id or null (default) to flush all caches
     * @return void
     */
    private static function _cacheFlush($cacheId = null)
    {
        if ($cacheId === null) {
            self::$_cache = array();
        } else {
            self::$_cache[$cacheId] = array();
        }
    }
}

