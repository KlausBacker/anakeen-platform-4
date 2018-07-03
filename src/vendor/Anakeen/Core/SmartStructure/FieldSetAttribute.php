<?php

/**
 * Structural attribute (attribute that contain other attribute : tab, frame)
 *
 */

namespace Anakeen\Core\SmartStructure;

class FieldSetAttribute extends BasicAttribute
{
    /**
     * Constructor
     *
     * @param string                                         $id       $docid famid
     * @param string                                         $docid
     * @param string                                         $label    default translation key
     * @param string                                         $access   visibility option
     * @param string                                         $usefor   Attr or Param usage
     * @param string                                         $type     kind of
     * @param \Anakeen\Core\SmartStructure\FieldSetAttribute $fieldSet parent field
     * @param string                                         $options  option string
     * @param string                                         $docname
     */
    public function __construct($id, $docid, $label, $access = "", $usefor = "", $type = "frame", &$fieldSet = null, $options = "", $docname = "")
    {
        $this->id = $id;
        $this->structureId = $docid;
        $this->labelText = $label;
        $this->access = $access;
        $this->usefor = $usefor;
        $this->type = $type;
        $this->fieldSet = & $fieldSet;
        $this->options = $options;
        $this->docname = $docname;
    }
    /**
     * Generate the xml schema fragment
     *
     * @param BasicAttribute[] $la
     *
     * @return string
     */
    public function getXmlSchema($la)
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/%s", DEFAULT_PUBDIR, "fieldattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $this->common_getXmlSchema($lay);
        
        $lay->set("minOccurs", "0");
        $lay->set("maxOccurs", "1");
        $lay->set("notop", ($this->fieldSet->id != '' && $this->fieldSet->id != \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD));
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

}

