<?php

/**
 * Structural attribute (attribute that contain other attribute : tab, frame)
 *
 * @author Anakeen
 *
 */
class FieldSetAttribute extends BasicAttribute
{
    /**
     * Constructor
     *
     * @param string $id $docid famid
     * @param string $docid
     * @param string $label default translation key
     * @param string $visibility visibility option
     * @param string $usefor Attr or Param usage
     * @param string $type kind of
     * @param FieldSetAttribute $fieldSet parent field
     * @param string $options option string
     * @param string $docname
     */
    public function __construct($id, $docid, $label, $visibility = "", $usefor = "", $type = "frame", &$fieldSet = null, $options = "", $docname = "")
    {
        $this->id = $id;
        $this->docid = $docid;
        $this->labelText = $label;
        $this->visibility = $visibility;
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
        $lay = new Layout(sprintf("%s/vendor/Anakeen/FDL/Layout/%s", DEFAULT_PUBDIR, "fieldattribute_schema.xml"));
        $lay->set("aname", $this->id);
        $this->common_getXmlSchema($lay);
        
        $lay->set("minOccurs", "0");
        $lay->set("maxOccurs", "1");
        $lay->set("notop", ($this->fieldSet->id != '' && $this->fieldSet->id != Adoc::HIDDENFIELD));
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
     * export values as xml fragment
     *
     * @param Doc $doc working doc
     * @param exportOptionAttribute $opt
     * @deprecated use \Dcp\ExportXmlDocument class instead
     *
     * @return string
     */
    public function getXmlValue(Doc & $doc, $opt = false)
    {
        $la = $doc->getAttributes();
        $xmlvalues = array();
        foreach ($la as $k => $v) {
            /**
             * @var NormalAttribute $v
             */
            if ($v->fieldSet && $v->fieldSet->id == $this->id && (empty($opt->exportAttributes[$doc->fromid]) || in_array($v->id, $opt->exportAttributes[$doc->fromid]))) {
                $value = $v->getXmlValue($doc, $opt);
                if ($v->type == "htmltext" && $opt !== false) {
                    $value = $v->prepareHtmltextForExport($value);
                    if ($opt->withFile) {
                        $value = preg_replace_callback('/(&lt;img.*?)src="(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/', function ($matches) use ($opt) {
                            if (isset($matches[7])) {
                                $docid = $matches[8];
                                $attrid = $matches[9];
                                $index = $matches[10] == "-1" ? 0 : $matches[10];
                            } else {
                                $docid = $matches[4];
                                $index = $matches[6] == "-1" ? 0 : $matches[6];
                                $attrid = $matches[5];
                            }
                            $doc = new_Doc(getDbAccess(), $docid);
                            $attr = $doc->getAttribute($attrid);
                            $tfiles = $doc->vault_properties($attr);
                            $f = $tfiles[$index];
                            if (is_file($f["path"])) {
                                if ($opt->outFile) {
                                    return sprintf('%s title="%s" src="data:%s;base64,[FILE64:%s]"', "\n" . $matches[1], unaccent($f["name"]), $f["mime_s"], $f["path"]);
                                } else {
                                    return sprintf('%s title="%s" src="data:%s;base64,%s"', "\n" . $matches[1], unaccent($f["name"]), $f["mime_s"], base64_encode(file_get_contents($f["path"])));
                                }
                            } else {
                                return sprintf('%s title="%s" src="data:%s;base64,file not found"', "\n" . $matches[1], unaccent($f["name"]), $f["mime_s"]);
                            }
                        }, $value);
                    }
                }
                $xmlvalues[] = $value;
            }
        }
        if ($opt->flat) {
            return implode("\n", $xmlvalues);
        } else {
            return sprintf("<%s>%s</%s>", $this->id, implode("\n", $xmlvalues), $this->id);
        }
    }
}

