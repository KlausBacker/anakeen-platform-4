<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\FieldAccessManager;

class ExportXmlDocument
{
    /**
     * @var \Anakeen\Core\Internal\SmartElement
     */
    protected $document = null;
    protected $exportProfil = false;

    protected $exportFiles = false;
    protected $exportDocumentNumericIdentiers = false;
    protected $attributeToExport = array();
    protected $includeSchemaReference = false;
    protected $structureAttributes;

    protected $verifyAttributeAccess = true;
    protected $writeToFile = false;

    /**
     * If true, attribute with "I" visibility are not returned
     *
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }

    /**
     * @param mixed $structureAttributes
     */
    public function setStructureAttributes($structureAttributes)
    {
        $this->structureAttributes = $structureAttributes;
    }

    /**
     * @param array $attributeToExport
     */
    public function setAttributeToExport($attributeToExport)
    {
        $this->attributeToExport = $attributeToExport;
    }

    /**
     * @param boolean $exportDocumentNumericIdentiers
     */
    public function setExportDocumentNumericIdentiers($exportDocumentNumericIdentiers)
    {
        $this->exportDocumentNumericIdentiers = $exportDocumentNumericIdentiers;
    }

    /**
     * @param boolean $exportFiles
     */
    public function setExportFiles($exportFiles)
    {
        $this->exportFiles = $exportFiles;
    }

    /**
     * @param boolean $includeSchemaReference
     */
    public function setIncludeSchemaReference($includeSchemaReference)
    {
        $this->includeSchemaReference = $includeSchemaReference;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    public function getXml()
    {
        if ($this->exportFiles) {
            throw new Exception("EXPC0103");
        }
        return $this->export();
    }

    public function writeTo($filePath)
    {
        $this->export($filePath);
    }

    protected function export($outfile = "")
    {
        $lay = new \Layout(sprintf("%s/vendor/Anakeen/Core/Layout/exportxml.xml", DEFAULT_PUBDIR));
        //$lay=&$this->document->lay;
        $lay->set("famname", strtolower($this->document->fromname));
        $lay->set("id", ($this->exportDocumentNumericIdentiers ? $this->document->id : ''));
        if ($this->document->locked != -1) {
            $lay->set("name", $this->document->name);
        } else {
            $lay->set("name", "");
        }
        $lay->set("revision", $this->document->revision);
        $lay->set("version", $this->document->getVersion());
        $lay->set("state", $this->document->getState());
        $lay->set("title", htmlspecialchars($this->document->getTitle(), ENT_QUOTES));
        $lay->set("mdate", $this->document->mdate);
        $lay->set("flat", (!$this->includeSchemaReference || !$this->structureAttributes));
        $la = $this->document->GetFieldAttributes();
        $level1 = array();

        foreach ($la as $k => $v) {
            if ((!$v) || ($v->getOption("autotitle") == "yes") || ($v->usefor == 'Q')) {
                unset($la[$k]);
            }
        }
        $option = new \ExportOptionAttribute();
        $option->outFile = $outfile;

        foreach ($la as $k => & $v) {
            if (($v->id != \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) && ($v->type == 'frame' || $v->type == "tab")
                && ((!$v->fieldSet)
                    || $v->fieldSet->id == \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD)) {
                $level1[] = array(
                    "level" => $this->getStructXmlValue($v)
                );
            }
        }
        $lay->setBlockData("top", $level1);
        if ($outfile) {
            $this->writeToFile = true;
            if ($this->exportFiles) {
                $xmlcontent = $lay->gen();
                $fo = fopen($outfile, "w");
                if (!$fo) {
                    throw new Exception("EXPC0101", $outfile);
                }
                $pos = strpos($xmlcontent, "[FILE64");

                $bpos = 0;
                while ($pos !== false) {
                    if (fwrite($fo, substr($xmlcontent, $bpos, $pos - $bpos))) {
                        $bpos = strpos($xmlcontent, "]", $pos) + 1;

                        $filepath = substr($xmlcontent, $pos + 8, ($bpos - $pos - 9));
                        /* If you want to encode a large file, you should encode it in chunks that
                                            are a multiple of 57 bytes.  This ensures that the base64 lines line up
                                            and that you do not end up with padding in the middle. 57 bytes of data
                                            fills one complete base64 line (76 == 57*4/3):*/
                        $ff = fopen($filepath, "r");
                        $size = 6 * 1024 * 57;
                        while ($buf = fread($ff, $size)) {
                            fwrite($fo, base64_encode($buf));
                        }
                        $pos = strpos($xmlcontent, "[FILE64", $bpos);
                    } else {
                        throw new Exception("EXPC0102", $outfile);
                    }
                }
                fwrite($fo, substr($xmlcontent, $bpos));
                fclose($fo);
            } else {
                if (file_put_contents($outfile, $lay->gen()) === false) {
                    throw new Exception("EXPC0100", $outfile);
                }
            }
        } else {
            $this->writeToFile = false;
            return $lay->gen();
        }
        return '';
    }

    /**
     * export values as xml fragment
     *
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $attribute
     * @param int                                          $indexValue (in case of multiple value)
     *
     * @return string
     */
    protected function getAttributeXmlValue(\Anakeen\Core\SmartStructure\NormalAttribute $attribute, $indexValue)
    {
        $doc = $this->document;
        if ($this->verifyAttributeAccess && !FieldAccessManager::hasReadAccess($this->document, $attribute)) {
            return sprintf("<%s granted=\"false\"/>", $attribute->id);
        }

        if ($indexValue > -1) {
            $v = $doc->getMultipleRawValues($attribute->id, null, $indexValue);
        } else {
            $v = $doc->getRawValue($attribute->id, null);
        }
        //if (! $v) return sprintf("<!-- no value %s -->",$attribute->id);
        if ($attribute->getOption("autotitle") == "yes") {
            return sprintf("<!--autotitle %s %s -->", $attribute->id, $v);
        }
        if (($v === null) && ($attribute->type != 'array')) {
            if (($attribute->type == 'file') || ($attribute->type == 'image')) {
                return sprintf('<%s mime="" title="" xsi:nil="true"/>', $attribute->id);
            } else {
                return sprintf('<%s xsi:nil="true"/>', $attribute->id);
            }
        }
        switch ($attribute->type) {
            case 'timestamp':
            case 'date':
                $v = stringDateToIso($v);
                return sprintf("<%s>%s</%s>", $attribute->id, $v, $attribute->id);
            case 'array':
                $av = $doc->getArrayRawValues($attribute->id);
                $axml = array();
                foreach ($av as $k => $col) {
                    $xmlvalues = array();
                    foreach ($col as $aid => $aval) {
                        $oa = $doc->getAttribute($aid);
                        if (empty($this->attributeToExport[$doc->fromid]) || in_array($aid, $this->attributeToExport[$doc->fromid])) {
                            $indexValue = $k;
                            $xmlvalues[] = $this->getAttributeXmlValue($oa, $indexValue);
                        }
                    }
                    $axml[] = sprintf("<%s>%s</%s>", $attribute->id, implode("\n", $xmlvalues), $attribute->id);
                }
                return implode("\n", $axml);
            case 'image':
            case 'file':
                if (preg_match(PREGEXPFILE, $v, $reg)) {
                    if ($this->exportDocumentNumericIdentiers) {
                        $vid = $reg[2];
                    } else {
                        $vid = '';
                    }
                    $mime = $reg[1];
                    $name = htmlspecialchars($reg[3], ENT_QUOTES);
                    $base = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_EXTERNURL");
                    $href = $base . str_replace('&', '&amp;', $doc->getFileLink($attribute->id));
                    if ($this->exportFiles) {
                        $path = $doc->vault_filename_fromvalue($v, true);

                        if (is_file($path)) {
                            if ($this->writeToFile) {
                                return sprintf('<%s vid="%s" mime="%s" title="%s">[FILE64:%s]</%s>', $attribute->id, $vid, $mime, $name, $path, $attribute->id);
                            } else {
                                return sprintf('<%s vid="%s" mime="%s" title="%s">%s</%s>', $attribute->id, $vid, $mime, $name, base64_encode(file_get_contents($path)),
                                    $attribute->id);
                            }
                        } else {
                            return sprintf('<!-- file not found --><%s vid="%s" mime="%s" title="%s"/>', $attribute->id, $vid, $mime, $name, $attribute->id);
                        }
                    } else {
                        return sprintf('<%s vid="%s" mime="%s" href="%s" title="%s"/>', $attribute->id, $vid, $mime, $href, $name);
                    }
                } else {
                    return sprintf("<%s>%s</%s>", $attribute->id, $v, $attribute->id);
                }
            // no break
            case 'thesaurus':
            case 'account':
            case 'docid':
                if (!$v) {
                    return sprintf('<%s xsi:nil="true"/>', $attribute->id);
                } else {
                    if ($attribute->isMultiple() && is_string($v) && $v[0] === '{') {
                        $v = SmartElement::rawValueToArray($v);
                    }
                    if (is_array($v)) {
                        $mName = array();
                        $mId = array();
                        $foundName = false;
                        foreach ($v as $id) {
                            if ($id) {
                                $lName = \Anakeen\Core\SEManager::getNameFromId($id);
                                $mName[] = $lName;
                                $mId[] = $id;
                                if ($lName) {
                                    $foundName = true;
                                }
                            }
                        }
                        $sIds = '';
                        if ($this->exportDocumentNumericIdentiers) {
                            $sIds = sprintf('id="%s"', implode(',', $mId));
                        }
                        $sName = '';
                        if ($foundName) {
                            $sName = sprintf('name="%s"', implode(',', $mName));
                        }
                        return sprintf('<%s %s %s>%s</%s>', $attribute->id, $sName, $sIds, _("multiple document"), $attribute->id);
                    } else {
                        $info = SEManager::getRawData($v, array(
                            "title",
                            "name",
                            "id",
                            "revision",
                            "initid",
                            "locked"
                        ), false);

                        if ($info) {
                            $docid = $info["id"];
                            $docRevOption = $attribute->getOption("docrev", "latest");
                            $latestTitle = ($docRevOption === "latest");

                            $revAttr = "";
                            if ($latestTitle) {
                                $docid = $info["initid"];
                                if ($info["locked"] == -1) {
                                    $info["title"] = $doc->getLastTitle($docid);
                                }
                            } elseif ($docRevOption === "fixed") {
                                $revAttr = sprintf(' revision="%d" ', $info["revision"]);
                            } elseif (preg_match('/^state\(([^\)]+)\)/', $docRevOption, $matches)) {
                                $revAttr = sprintf(' revision="state:%s" ', htmlspecialchars($matches[1], ENT_QUOTES));
                            }

                            if ($info["name"]) {
                                $info["name"] = htmlspecialchars($info["name"], ENT_QUOTES);

                                if ($this->exportDocumentNumericIdentiers) {
                                    return sprintf(
                                        '<%s id="%s" name="%s"%s>%s</%s>',
                                        $attribute->id,
                                        $docid,
                                        $info["name"],
                                        $revAttr,
                                        $attribute->encodeXml($info["title"]),
                                        $attribute->id
                                    );
                                } else {
                                    if ($revAttr) {
                                        \Anakeen\Core\Utils\System::addWarningMsg(
                                            sprintf(
                                                _("Doc %s : Attribut \"%s\" reference revised identifier : importation not support revision links without identifiers"),
                                                $doc->getTitle(),
                                                $attribute->getLabel()
                                            )
                                        );
                                    }
                                    return sprintf('<%s name="%s"%s>%s</%s>', $attribute->id, $info["name"], $revAttr, $attribute->encodeXml($info["title"]), $attribute->id);
                                }
                            } else {
                                if ($this->exportDocumentNumericIdentiers) {
                                    return sprintf('<%s id="%s"%s>%s</%s>', $attribute->id, $docid, $revAttr, $attribute->encodeXml($info["title"]), $attribute->id);
                                } else {
                                    return sprintf('<%s>%s</%s>', $attribute->id, $attribute->encodeXml($info["title"]), $attribute->id);
                                }
                            }
                        } else {

                            return sprintf('<%s id="%s">%s</%s>', $attribute->id, $v, _("unreferenced document"), $attribute->id);

                        }
                    }
                }
            // no break

            case 'longtext':
                return sprintf("<%s><![CDATA[%s]]></%s>", $attribute->id, $v, $attribute->id);
                break;
            default:
                return sprintf("<%s>%s</%s>", $attribute->id, $attribute->encodeXml($v), $attribute->id);
        }
    }

    protected function getXmlValue(\Anakeen\Core\SmartStructure\BasicAttribute $attribute, $indexValue)
    {
        if ($attribute->isNormal === true) {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute
             */
            return $this->getAttributeXmlValue($attribute, $indexValue);
        } else {
            /**
             * @var \Anakeen\Core\SmartStructure\FieldSetAttribute $attribute
             */
            return $this->getStructXmlValue($attribute, $indexValue);
        }
    }

    /**
     * export values as xml fragment
     *
     * @param \Anakeen\Core\SmartStructure\FieldSetAttribute $structAttribute
     * @param int                                            $indexValue
     *
     * @return string
     */
    protected function getStructXmlValue(\Anakeen\Core\SmartStructure\FieldSetAttribute $structAttribute, $indexValue = -1)
    {
        $doc = $this->document;
        $la = $doc->getAttributes();
        $xmlvalues = array();
        foreach ($la as $k => $v) {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $v
             */
            if ($v->fieldSet && $v->fieldSet->id == $structAttribute->id
                && (empty($this->attributeToExport[$doc->fromid])
                    || in_array($v->id, $this->attributeToExport[$doc->fromid]))) {
                $value = $this->getXmlValue($v, $indexValue);
                if ($v->type == "htmltext" && $this->exportFiles) {
                    $value = $v->prepareHtmltextForExport($value);
                    if ($this->exportFiles) {
                        $value = preg_replace_callback(
                            '/(&lt;img.*?)src="(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/',
                            function ($matches) {
                                if (isset($matches[7])) {
                                    $docid = $matches[8];
                                    $attrid = $matches[9];
                                    $index = $matches[10] == "-1" ? 0 : $matches[10];
                                } else {
                                    $docid = $matches[4];
                                    $index = $matches[6] == "-1" ? 0 : $matches[6];
                                    $attrid = $matches[5];
                                }
                                $docimg = SEManager::getDocument($docid);
                                $attr = $docimg->getAttribute($attrid);
                                $tfiles = $docimg->vault_properties($attr);
                                $f = $tfiles[$index];
                                $f["name"] = htmlspecialchars($f["name"], ENT_QUOTES);
                                if (is_file($f["path"])) {
                                    if ($this->writeToFile) {
                                        return sprintf('%s title="%s" src="data:%s;base64,[FILE64:%s]"', "\n" . $matches[1], \Anakeen\Core\Utils\Strings::Unaccent($f["name"]),
                                            $f["mime_s"], $f["path"]);
                                    } else {
                                        return sprintf(
                                            '%s title="%s" src="data:%s;base64,%s"',
                                            "\n" . $matches[1],
                                            \Anakeen\Core\Utils\Strings::unaccent($f["name"]),
                                            $f["mime_s"],
                                            base64_encode(file_get_contents($f["path"]))
                                        );
                                    }
                                } else {
                                    return sprintf('%s title="%s" src="data:%s;base64,file not found"', "\n" . $matches[1], \Anakeen\Core\Utils\Strings::Unaccent($f["name"]),
                                        $f["mime_s"]);
                                }
                            },
                            $value
                        );
                    }
                }
                $xmlvalues[] = $value;
            }
        }
        if (!$this->structureAttributes) {
            return implode("\n", $xmlvalues);
        } else {
            return sprintf("<%s>%s</%s>", $structAttribute->id, implode("\n", $xmlvalues), $structAttribute->id);
        }
    }
}
