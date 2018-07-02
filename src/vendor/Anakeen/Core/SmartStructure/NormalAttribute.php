<?php

/**
 * NormalAttribute Class
 * Non structural attribute (all attribute except frame and tab)
 *
 * @author Anakeen
 *
 */

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\EnumManager;
use Anakeen\Core\Internal\Format\DateAttributeValue;

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
    protected $originalPhpfile;
    protected $originalPhpfunc;
    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Normal Attribute constructor : non structural attribute
     *
     * @param int                                            $id            id of the attribute
     * @param int                                            $docid         id of the family
     * @param string                                         $label         default translate key
     * @param string                                         $type          kind of attribute
     * @param string                                         $format        format option
     * @param string                                         $repeat        is repeteable attr
     * @param int                                            $order         display order
     * @param string                                         $link          link option
     * @param string                                         $visibility    visibility option
     * @param bool                                           $needed        is mandotary attribute
     * @param bool                                           $isInTitle     is used to compute title
     * @param bool                                           $isInAbstract  is used in abstract view
     * @param \Anakeen\Core\SmartStructure\FieldSetAttribute &$fieldSet     parent attribute
     * @param string                                         $phpfile       php file used with the phpfunc
     * @param string                                         $phpfunc       helpers function
     * @param string                                         $elink         eling option
     * @param string                                         $phpconstraint class php function
     * @param string                                         $usefor        Attribute or Parameter
     * @param string                                         $eformat       eformat option
     * @param string                                         $options       option string
     * @param string                                         $docname
     */
    public function __construct(
        $id,
        $docid,
        $label,
        $type,
        $format,
        $repeat,
        $order,
        $link,
        $visibility,
        $needed = false,
        $isInTitle = false,
        $isInAbstract = false,
        &$fieldSet = null,
        $phpfile = "",
        $phpfunc = "",
        $elink = "",
        $phpconstraint = "",
        $usefor = "",
        $eformat = "",
        $options = "",
        $docname = "",
        $prop = ""
    ) {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->type = $type;
        $this->format = $format;
        $this->eformat = $eformat;
        $this->ordered = $order;
        $this->link = $link;
        $this->access = $visibility;
        $this->needed = $needed;
        $this->isInTitle = $isInTitle;
        $this->isInAbstract = $isInAbstract;
        $this->fieldSet = &$fieldSet;
        $this->phpfile = $phpfile;
        $this->phpfunc = $phpfunc;
        $this->elink = $elink;
        $this->phpconstraint = $phpconstraint;
        $this->usefor = $usefor;
        $this->repeat = $repeat;
        $this->options = $options;
        $this->docname = $docname;
        if ($prop) {
            $prop = str_replace(['\\', '\\\\"'], ['\\\\', '\\"'], $prop);
            $this->properties = json_decode($prop);
        }
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
                $name = \Anakeen\Core\SEManager::getNameFromId($matches[2]);
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
                return $this->text_getXmlSchema();
            case 'longtext':
            case 'htmltext':
                return $this->longtext_getXmlSchema();
            case 'int':
            case 'integer':
                return $this->int_getXmlSchema();
            case 'float':
            case 'money':
                return $this->float_getXmlSchema();
            case 'image':
            case 'file':
                return $this->file_getXmlSchema();
            case 'enum':
                return $this->enum_getXmlSchema();
            case 'thesaurus':
            case 'docid':
            case 'account':
                return $this->docid_getXmlSchema();
            case 'date':
                return $this->date_getXmlSchema();
            case 'timestamp':
                return $this->timestamp_getXmlSchema();
            case 'time':
                return $this->time_getXmlSchema();
            case 'array':
                return $this->array_getXmlSchema($la);
            case 'color':
                return $this->color_getXmlSchema();
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
        $lay->set("visibility", $this->access);
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
    protected function text_getXmlSchema()
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
    protected function enum_getXmlSchema()
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
    protected function docid_getXmlSchema()
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
    protected function date_getXmlSchema()
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
    protected function timestamp_getXmlSchema()
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
    protected function color_getXmlSchema()
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
    protected function int_getXmlSchema()
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
    protected function longtext_getXmlSchema()
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
    protected function float_getXmlSchema()
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
    protected function time_getXmlSchema()
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
    protected function file_getXmlSchema()
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
    protected function array_getXmlSchema(&$la)
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
     * @param \Anakeen\Core\Internal\SmartElement $doc           current Doc
     * @param int                                 $index         index if multiple
     * @param array                               $configuration value config array :
     *                                                           dateFormat => 'US' 'ISO',
     *                                                           decimalSeparator => '.',
     *                                                           longtextMultipleBrToCr => ' '
     *                                                           multipleSeparator => array(0 => 'arrayLine', 1 => 'multiple')
     *
     * (defaultValue : dateFormat : 'US', decimalSeparator : '.', multiple => array(0 => "\n", 1 => ", "))
     *
     * @return string
     */
    public function getTextualValue(\Anakeen\Core\Internal\SmartElement $doc, $index = -1, array $configuration = array())
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
        $fc = new \Anakeen\Core\Internal\FormatCollection();
        $stripHtmlTags = isset($configuration['stripHtmlTags']) ? $configuration['stripHtmlTags'] : false;
        $fc->stripHtmlTags($stripHtmlTags);

        $fc->setDecimalSeparator($decimalSeparator);
        $fc->setDateStyle(DateAttributeValue::defaultStyle);
        $dateFormat = isset($configuration['dateFormat']) ? $configuration['dateFormat'] : 'US';

        if ($dateFormat == 'US') {
            $fc->setDateStyle(DateAttributeValue::isoWTStyle);
        } elseif ($dateFormat == "ISO") {
            $fc->setDateStyle(DateAttributeValue::isoStyle);
        } elseif ($dateFormat == 'FR') {
            $fc->setDateStyle(DateAttributeValue::frenchStyle);
        } else {
            $fc->setDateStyle(DateAttributeValue::defaultStyle);
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
        return \Anakeen\Core\Internal\FormatCollection::getDisplayValue($info, $this, $index, $configuration);
    }

    public function getNumberValue(\Anakeen\Core\Internal\SmartElement $doc, $index = -1, $decimalSeparator = ".")
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

        $enumItems = EnumManager::getEnums($this->format);
        $labels = [];
        foreach ($enumItems as $key => $item) {
            if (isset($item["path"])) {
                $key = $item["path"];
            }
            $labels[$key] = $item["label"];
        }
        return $labels;
    }


    /**
     * return array of enumeration definition
     * the array'skeys are the enum single key and the values are the complete labels
     *
     * @param string $enumid         the key of enumerate (if no parameter all labels are returned
     * @param bool   $returnDisabled if false disabled enum are not returned
     * @return array|string|null
     */
    public function getEnumLabel($enumid = null, $returnDisabled = true)
    {
        if ($enumid !== null) {
            $item = EnumManager::getEnumItem($this->format, $enumid, $returnDisabled);
            if ($item) {
                if (!empty($item["longLabel"])) {
                    return $item["longLabel"];
                }
                return $item["label"];
            } else {
                return null;
            }
        } else {
            $enumItems = EnumManager::getEnums($this->format);
            $labels = [];
            foreach ($enumItems as $key => $item) {
                $labels[$key] = (isset($item["longLabel"])) ? $item["longLabel"] : $item["label"];
            }
            return $labels;
        }
    }

    /**
     * add new \item in enum list items
     *
     * @param string $key   database key
     * @param string $label human label
     *
     * @return string error message (empty means ok)
     */
    public function addEnum($key, $label)
    {
        return EnumManager::addEnum($this->format, $key, $label);
    }


    /**
     * Test if an enum key exists
     *
     * @param string $key         enumKey
     * @param bool   $completeKey if true test complete key with path else without path
     * @return bool
     */
    public function existEnum($key)
    {
        if ($key == "") {
            return false;
        }

        return EnumManager::getEnumItem($this->format, $key) !== null;
    }
}
