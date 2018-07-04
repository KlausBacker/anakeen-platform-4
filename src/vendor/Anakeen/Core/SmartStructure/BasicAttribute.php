<?php
/**
 * Smart Element Field
 *
 */

namespace Anakeen\Core\SmartStructure;

/**
 *
 * Generic attribute class
 *
 * @author Anakeen
 */
class BasicAttribute
{
    const hiddenFieldId = \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD;
    const NONE_ACCESS = 0;
    const READ_ACCESS = 1;
    const WRITE_ACCESS = 2;
    const READWRITE_ACCESS = 3;
    public $id;
    public $structureId;
    public $labelText;
    public $access; // Write, Read, None
    public $options;
    public $docname;
    public $type; // text, longtext, date, file, ...
    public $usefor; // = Q if parameters.
    public $ordered; // order to place attributes
    public $format; // subtypepublic
    public $isNormal = null;
    /**
     * @var AttributeOptions
     */
    public $properties = null;
    /**
     * @var \Anakeen\Core\SmartStructure\FieldSetAttribute field set object
     */
    public $fieldSet;
    /**
     * @var array
     */
    public $_topt = null;

    /**
     * Construct a basic attribute
     *
     * @param string $id logical name of the attr
     * @param string $docid
     * @param string $label
     */
    public function __construct($id, $docid, $label)
    {
        $this->id = $id;
        $this->structureId = $docid;
        $this->labelText = $label;
    }

    /**
     * Return attribute label
     *
     * @return string
     */
    public function getLabel()
    {
        $r = $this->docname . '#' . $this->id;
        $i = _($r);
        if ($i != $r) {
            return $i;
        }
        return $this->labelText;
    }

    /**
     * Get access depending of set fields access
     */
    public function getAccess()
    {
        return (!$this->fieldSet) ? $this->access : $this->fieldSet->getAccess() & $this->access;
    }

    /**
     * Return value of option $x
     *
     * @param string $x   option name
     * @param string $def default value
     *
     * @return string
     */
    public function getOption($x, $def = "")
    {
        if (!isset($this->_topt)) {
            $topt = explode("|", $this->options);
            $this->_topt = array();
            foreach ($topt as $k => $v) {
                if ($v) {
                    $v = explode("=", $v, 2);
                    $this->_topt[$v[0]] = isset($v[1]) ? $v[1] : null;
                }
            }
        }
        $r = $this->docname . '#' . $this->id . '#' . $x;
        $i = _($r);
        if ($i != $r) {
            return $i;
        }

        $v = (isset($this->_topt[$x]) && $this->_topt[$x] !== '') ? $this->_topt[$x] : $def;
        return $v;
    }

    /**
     * Return all value of options
     *
     * @return array
     */
    public function getOptions()
    {
        if (!isset($this->_topt)) {
            $this->getOption('a');
        }
        return $this->_topt;
    }

    /**
     * Temporary change option
     *
     * @param string $x name
     * @param string $v value
     *
     * @return void
     */
    public function setOption($x, $v)
    {
        if (!isset($this->_topt)) {
            $this->getOption($x);
        }
        $this->_topt[$x] = $v;
    }


    /**
     * test if attribute is not a auto created attribute
     *
     * @return boolean
     */
    public function isReal()
    {
        return $this->getOption("autocreated") != "yes";
    }

    /**
     * Escape value with xml entities
     *
     * @param string $s    value
     * @param bool   $quot to encode also quote "
     * @return string
     */
    public static function encodeXml($s, $quot = false)
    {
        if ($quot) {
            return str_replace(array(
                '&',
                '<',
                '>',
                '"'
            ), array(
                '&amp;',
                '&lt;',
                '&gt;',
                '&quot;'
            ), $s);
        } else {
            return str_replace(array(
                '&',
                '<',
                '>'
            ), array(
                '&amp;',
                '&lt;',
                '&gt;'
            ), $s);
        }
    }

    /**
     * to see if an attribute is n item of an array
     *
     * @return boolean
     */
    public function inArray()
    {
        return false;
    }

    /**
     * verify if accept multiple value
     *
     * @return boolean
     */
    public function isMultiple()
    {
        return ($this->inArray() || ($this->getOption('multiple') === 'yes'));
    }

    /**
     * verify if attribute is multiple value and if is also in array multiple^2
     *
     * @return boolean
     */
    public function isMultipleInArray()
    {
        return ($this->inArray() && ($this->getOption('multiple') === 'yes'));
    }



    /**
     * Export values as xml fragment
     *
     * @param array $la array of DocAttribute
     * @return string
     */
    public function getXmlSchema($la)
    {
        return sprintf("<!-- no Schema %s (%s)-->", $this->id, $this->type);
    }

    /**
     * export values as xml fragment
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc working doc
     * @param bool|\exportOptionAttribute         $opt
     * @deprecated use \Dcp\ExportXmlDocument class instead
     *
     * @return string
     */
    public function getXmlValue(\Anakeen\Core\Internal\SmartElement & $doc, $opt = false)
    {
        return sprintf("<!-- no value %s (%s)-->", $this->id, $this->type);
    }

    /**
     * Get human readable textual value
     * Fallback method
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc           current Doc
     * @param int                                 $index         index if multiple
     * @param array                               $configuration value
     *
     * @return string
     */
    public function getTextualValue(\Anakeen\Core\Internal\SmartElement $doc, $index = -1, array $configuration = array())
    {
        return null;
    }

    /**
     * Generate XML schema layout
     *
     * @param \Layout $play
     */
    public function commonGetXmlSchema(&$play)
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "infoattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $lay->set("label", $this->encodeXml($this->labelText));
        $lay->set("type", $this->type);
        $lay->set("isTitle", false);
        $lay->set("phpfile", false);
        $lay->set("phpfunc", false);

        $lay->set("computed", false);
        $lay->set("link", '');
        $lay->set("elink", '');
        $lay->set("default", false);
        $lay->set("constraint", '');
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

        $play->set("minOccurs", "0");
        $play->set("isnillable", "true");
        $play->set("maxOccurs", "1");
        $play->set("aname", $this->id);
        $play->set("appinfos", $lay->gen());
    }
}
