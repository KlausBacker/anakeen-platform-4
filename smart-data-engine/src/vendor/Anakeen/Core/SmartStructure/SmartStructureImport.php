<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generation of PHP Document classes
 *
 * @author  Anakeen
 * @package FDL
 * @subpackage
 */
/**
 */

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\Settings;
use Anakeen\Core\Utils\MiscDoc;
use Anakeen\Exception;
use Anakeen\LogManager;

class SmartStructureImport
{
    /**
     * Write PHP content to destination file if PHP syntax is correct.
     *
     * - The content is first written to a temporary file next to the
     *   final destination file.
     * - The syntax of the temporary file is checked.
     * - If the syntax is correct, then the temporary file is "commited"
     *   to the destination file.
     *
     * @param string $fileName destination file
     * @param string $content content to write
     *
     * @return string empty string on success or error message on failure
     */
    protected static function __phpLintWriteFile($fileName, $content)
    {
        $dir = dirname($fileName);
        $temp = tempnam($dir, basename($fileName) . '.tmp');
        if ($temp === false) {
            return sprintf(_("Error creating temporary file in '%s'."), $dir);
        }
        if (file_put_contents($temp, $content) === false) {
            unlink($temp);
            return sprintf(_("Error writing content to file '%s'."), $temp);
        }
        if (\CheckClass::phpLintFile($temp, $output) === false) {
            // Leave temp file for syntax error analysis
            return sprintf(_("Syntax error in file '%s': %s"), $temp, join("\n", $output));
        }
        if (rename($temp, $fileName) === false) {
            unlink($temp);
            return sprintf(_("Error renaming '%s' to '%s'."), $temp, $fileName);
        }
        return '';
    }

    /**
     * Generate Class.Docxxx.php files
     *
     * @param string $genDir output directory
     * @param array $tdoc array of family definition
     *
     * @return void
     * @throws Exception
     */
    protected static function generateFamilyPhpClass($genDir, $tdoc)
    {
        $phpAdoc = new \Anakeen\Layout\TextLayout();

        if ($tdoc["classname"] == "") { // default classname
            if ($tdoc["fromid"] == 0) {
                $tdoc["classname"] = '\\' . \Anakeen\SmartElement::class;
            } else {
                $tdoc["classname"] = "Doc" . $tdoc["fromid"];
            }
        } else {
            $tdoc["classname"] = '\\' . $tdoc["classname"];
        }
        if ($tdoc["fromid"] > 0) {
            $fromName = \Anakeen\Core\SEManager::getNameFromId($tdoc["fromid"]);
            if ($fromName == '') {
                throw new \Anakeen\Exception("FAM0601", $tdoc["fromid"], $tdoc["name"]);
            }
            $tdoc["fromname"] = $fromName;
            $phpAdoc->Set("fromFile", ucfirst(strtolower($fromName)));
        } else {
            $tdoc["fromname"] = "Document";
        }

        $tdoc["docFile"] = ucfirst(strtolower($tdoc["name"]));
        $phpAdoc->Set("docFile", $tdoc["docFile"]);
        $phpAdoc->Set("fromname", $tdoc["fromname"]);
        $phpAdoc->Set("docid", $tdoc["id"]);
        $phpAdoc->Set("include", "");
        $phpAdoc->Set("GEN", "");
        if ($tdoc["fromid"] == 0) {
            $phpAdoc->Set("DocParent", $tdoc["classname"]);
            $phpAdoc->Set("AParent", '\\' . \Anakeen\Core\SmartStructure\Attributes::class);
            $phpAdoc->Set("fromid", "");
            $phpAdoc->Set("pinit", '\DocCtrl');
        } else {
            $parentFile = sprintf(
                "%s/%s/SmartStructure/Smart%d.php",
                DEFAULT_PUBDIR,
                Settings::DocumentGenDirectory,
                $tdoc["fromid"]
            );
            if ((!file_exists($parentFile)) || filesize($parentFile) == 0) {
                throw new \Anakeen\Exception("FAM0600", $parentFile, $tdoc["name"]);
            }
            $phpAdoc->Set("fromid", $tdoc["fromid"]);
            if ($tdoc["classname"] != "Doc" . $tdoc["fromid"]) {
                $phpAdoc->Set("DocParent", $tdoc["classname"]);
                $phpAdoc->Set("pinit", $tdoc["classname"]);
                $phpAdoc->Set("include", sprintf('require_once(__DIR__."/%s.php");', $tdoc["fromname"]));
            } else {
                $phpAdoc->Set("GEN", "GEN");
                if ($tdoc["name"]) {
                    $phpAdoc->Set("DocParent", SEManager::getFamilyClassName($tdoc["fromname"]));
                } else {
                    $phpAdoc->Set("DocParent", '\\Doc' . $tdoc["fromid"]);
                }
            }
            $phpAdoc->Set("AParent", SEManager::getAttributesClassName($tdoc["fromname"]));
        }
        $phpAdoc->Set("title", $tdoc["title"]);
        $query = new \Anakeen\Core\Internal\QueryDb("", DocAttr::class);
        $query->AddQuery("docid=" . $tdoc["id"]);
        $query->order_by = "ordered";

        $docDbAttrs = $query->Query();

        $phpAdoc->Set("sattr", "");

        $phpAdoc->set("hasattr", false);
        $pa = self::getParentAttributes("", $tdoc["fromid"]);
        $allAttributes = [];
        if ($query->nb > 0) {
            $tmenu = array();
            $tfield = array();
            $tnormal = array();
            $tattr = array();
            $attrids = array();
            $tcattr = array();
            $taction = array();
            /**
             * @var $v DocAttr
             */
            /**
             * @var $v DocAttr
             */
            $table1 = [];
            foreach ($docDbAttrs as $k => $v) {
                $table1[strtolower($v->id)] = $v;
            }


            foreach ($table1 as $k => $v) {
                $type = trim(strtok($v->type, "("));
                if ($type === "docid" || $type == "account" || $type == "thesaurus") {
                    $parentDoctitle = "";
                    if (isset($pa[substr($v->id, 1)]) && preg_match(
                        "/doctitle=([A-Za-z0-9_-]+)/",
                        $pa[substr($v->id, 1)]["options"],
                        $reg
                    )) {
                        $parentDoctitle = $reg[1];
                    }
                    // add title auto
                    if ($v->usefor !== 'Q' && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $v->options, $reg)) {
                        $doctitle = $reg[1];
                        if ($doctitle === $parentDoctitle) {
                            continue;
                        }
                        if ($doctitle == "auto") {
                            $doctitle = $v->id . "_title";
                        }
                        $doctitle = strtolower($doctitle);

                        if (!isset($table1[strtolower($doctitle)])) {
                             $table1[$doctitle] = self::getDoctitleAttr($v, $doctitle);
                        }
                        if (empty($table1[$doctitle]->phpfunc)) {
                            if (!preg_match("/docrev=(fixed|state)/", $v->options)) {
                                $table1[$doctitle]->phpfunc = "::getLastTitle(" . $v->id . ",' )";
                            } else {
                                $table1[$doctitle]->phpfunc = "::getTitle(" . $v->id . ",' )";
                            }
                        }
                    }
                }
            }
            $pM = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
            foreach ($pa as $parentAttr) {
                $previousOrder = ""; //FamilyAbsoluteOrder::autoOrder;
                if (preg_match("/relativeOrder=([A-Za-z0-9_:-]+)/", $parentAttr["options"], $reg)) {
                    $previousOrder = strtolower($reg[1]);
                }
                if ($parentAttr["id"][0] !== ":") {
                    $allAttributes[$parentAttr["id"] . "/" . $parentAttr["docid"]] = [
                        "id" => $parentAttr["id"],
                        "parent" => $parentAttr["frameid"],
                        "family" => $parentAttr["docid"],
                        "prev" => $previousOrder,
                        "numOrder" => intval($parentAttr["ordered"])
                    ];
                    if (!$previousOrder) {
                        // Need to copy child attribute when use absolute orders
                        $allAttributes[$parentAttr["id"] . "/" . $tdoc["id"]] = $allAttributes[$parentAttr["id"] . "/" . $parentAttr["docid"]];
                        $allAttributes[$parentAttr["id"] . "/" . $tdoc["id"]]["family"] = $tdoc["id"];
                    }
                    if (is_numeric($parentAttr["ordered"])) {
                        $pattern = sprintf("/%s\\/([0-9]+)/", substr($parentAttr["id"], 1));

                        foreach ($allAttributes as $ka => $attrData) {
                            if (preg_match($pattern, $ka, $reg)) {
                                // Need to update parent also
                                if ($parentAttr["frameid"]) {
                                    $allAttributes[$ka]["parent"] = $parentAttr["frameid"];
                                }
                                $allAttributes[$ka]["numOrder"] = $parentAttr["ordered"];
                            }
                        }
                    }
                } else {
                    $mAttrid = substr($parentAttr["id"], 1);
                    foreach ($allAttributes as $ka => $attr) {
                        if ($attr["id"] === $mAttrid) {
                            if (!empty($parentAttr["frameid"])) {
                                $kap = $mAttrid . "/" . $parentAttr["docid"];
                                $allAttributes[$ka]["parent"] = $parentAttr["frameid"];
                                $allAttributes[$kap] = $allAttributes[$ka];
                                $allAttributes[$kap]["family"] = $parentAttr["docid"];
                            }
                        }
                    }
                }
            }

            foreach ($table1 as $k => $v) {
                $validOrder = true;
                if ($v->id[0] === ':') {
                    if (!$v->ordered && !$v->frameid) {
                        $validOrder = false;
                    }
                    $v = self::completeAttribute("", $v);
                    if (is_numeric($v->ordered)) {
                        $pattern = sprintf("/%s\\/([0-9]+)/", $v->id);
                        foreach ($allAttributes as $ka => $attrData) {
                            if (preg_match($pattern, $ka, $reg)) {
                                if ($v->frameid) {
                                    $allAttributes[$ka]["parent"] = $v->frameid;
                                }
                                $allAttributes[$ka]["numOrder"] = $v->ordered;
                            }
                        }
                    }
                }

                $previous = ""; //FamilyAbsoluteOrder::autoOrder;
                if (preg_match("/relativeOrder=([A-Za-z0-9_:-]+)/", $v->options, $reg)) {
                    $previous = strtolower($reg[1]);
                }
                if ($validOrder) {
                    $allAttributes[$v->id . "/" . $v->docid] = [
                        "id" => $v->id,
                        "parent" => $v->frameid,
                        "family" => $v->docid,
                        "prev" => $previous,
                        "numOrder" => intval($v->ordered)
                    ];
                }

                if ($v->type == "integer") {
                    $v->type = "int";
                } // old notation compliant
                //$v->phpfunc = str_replace("\"", "\\\"", $v->phpfunc);
                switch (strtolower($v->type)) {
                    case "tab":
                    case "frame": // frame
                        $tfield[strtolower($v->id)] = array(
                            "attrid" => strtolower($v->id),
                            "access" => FieldAccessManager::getRawAccess($v->accessibility),
                            "label" => str_replace("\"", "\\\"", $v->labeltext),
                            "usefor" => $v->usefor,
                            "type" => $v->type,
                            "options" => str_replace("\"", "\\\"", $v->options),
                            "frame" => ($v->frameid == "") ? \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD : strtolower($v->frameid),
                            "props" => str_replace('"', '\\"', str_replace(['\\"'], ['\\\\"'], $v->properties))
                        );
                        break;


                    default: // normal
                        if (preg_match('/^\[([a-z=0-9]+)](.*)/', $v->phpfunc, $reg)) {
                            $v->phpfunc = $reg[2];
                            $funcformat = $reg[1];
                        } else {
                            $funcformat = "";
                        }

                        if (preg_match("/([a-z]+)\\([\"'](.*)[\"']\\)/i", $v->type, $reg)) {
                            $atype = $reg[1];
                            $aformat = $reg[2];
                            if ($atype == "idoc") {
                                if (!is_numeric($aformat)) {
                                    $aformat = \Anakeen\Core\SEManager::getFamilyIdFromName($aformat);
                                }
                            }
                        } else {
                            $atype = $v->type;
                            $aformat = "";
                        }
                        $repeat = "false";

                        if (isset($tnormal[strtolower($v->frameid)])) {
                            if (self::getTypeMain($tnormal[strtolower($v->frameid)]["type"]) == "array") {
                                $repeat = "true";
                            }
                        }
                        if (($repeat == "false") && isset($pa[strtolower($v->frameid)])) {
                            if (self::getTypeMain($pa[strtolower($v->frameid)]["type"]) == "array") {
                                $repeat = "true";
                            }
                        }

                        if (strpos($v->options, "multiple=yes") !== false) {
                            $repeat = "true";
                        }

                        $atype = strtolower(trim($atype));
                        // create code for calculated attributes
                        if ((!$v->phpfile) && preg_match(
                            '/^(?:(?:[a-z_][a-z0-9_]*\\\\)*[a-z_][a-z0-9_]*)?::[a-z_][a-z0-9_]*\(/i',
                            $v->phpfunc,
                            $reg
                        ) && ($v->usefor != 'Q')) {
                            $pM->parse($v->phpfunc);
                            $error = $pM->getError();
                            if ($error) {
                                throw new \Anakeen\Exception($error);
                            }
                            if (!$pM->outputString) {
                                $oAid = $v->id;
                            } else {
                                $oAid = $pM->outputs[0];
                            }
                            $tcattr[] = array(
                                "callmethod" => self::doubleslash($v->phpfunc),
                                "callattr" => $oAid
                            );
                        }
                        // complete attributes characteristics
                        $v->id = chop(strtolower($v->id));

                        if (!$v->phpconstraint) {
                            if (($atype == "integer") || ($atype == "int")) {
                                $v->phpconstraint = sprintf("Anakeen\Core\Utils\Numbers::isInteger(%s)", $v->id);
                            } elseif (($atype == "money") || ($atype == "double")) {
                                $v->phpconstraint = sprintf("Anakeen\Core\Utils\Numbers::isFloat(%s)", $v->id);
                            }
                        }

                        $tnormal[($v->id)] = array(
                            "attrid" => ($v->id),
                            "label" => str_replace("\"", "\\\"", $v->labeltext),
                            "type" => $atype,
                            "format" => str_replace("\"", "\\\"", $aformat),
                            "eformat" => str_replace("\"", "\\\"", $funcformat),
                            "options" => self::doubleslash($v->options),
                            //(str_replace("\"", "\\\"", $v->options) ,
                            "order" => intval($v->ordered),
                            "link" => str_replace("\"", "\\\"", $v->link),
                            "access" => FieldAccessManager::getRawAccess($v->accessibility),
                            "needed" => ($v->needed == "Y") ? "true" : "false",
                            "title" => ($v->title == "Y") ? "true" : "false",
                            "repeat" => $repeat,
                            "abstract" => ($v->abstract == "Y") ? "true" : "false",
                            "frame" => ($v->frameid == "") ? \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD : strtolower($v->frameid),
                            "elink" => $v->elink,
                            "phpfile" => $v->phpfile,
                            "phpfunc" => self::doubleslash(str_replace(", |", ",  |", $v->phpfunc)),
                            "phpconstraint" => str_replace("\"", "\\\"", $v->phpconstraint),
                            "usefor" => $v->usefor,
                            "props" => str_replace('"', '\\"', str_replace(['\\"'], ['\\\\"'], $v->properties))
                        );

                        if (($atype != "array") && ($v->usefor != "Q")) {
                            if ($atype != "array") {
                                $tattr[$v->id] = array(
                                    "attrid" => ($v->id)
                                );
                            }

                            switch ($atype) {
                                case 'double':
                                case 'float':
                                case 'money':
                                    $attrids[$v->id] = ($v->id) . " float8";
                                    break;

                                case 'int':
                                case 'integer':
                                    $attrids[$v->id] = ($v->id) . " int4";
                                    break;

                                case 'date':
                                    $attrids[$v->id] = ($v->id) . " date";
                                    break;

                                case 'timestamp':
                                    $attrids[$v->id] = ($v->id) . " timestamp without time zone";
                                    break;

                                case 'time':
                                    $attrids[$v->id] = ($v->id) . " time";
                                    break;
                                case 'xml':
                                    $attrids[$v->id] = ($v->id) . " xml";
                                    break;
                                case 'json':
                                    $attrids[$v->id] = ($v->id) . " jsonb";
                                    break;

                                default:
                                    $attrids[$v->id] = ($v->id) . " text";
                            }
                            if ($repeat === "true") {
                                $attrids[$v->id] .= '[]';
                            }
                        }
                }
            }

            try {
                SmartFieldAbsoluteOrder::completeForNumericOrder($allAttributes, $tdoc["id"]);
                $absoluteOrders = SmartFieldAbsoluteOrder::getAbsoluteOrders($allAttributes, $tdoc["id"]);

                $tAbsOrders = [];
                foreach ($absoluteOrders as $kOrder => $attrid) {
                    $tAbsOrders[] = sprintf('"%s"=>%d', $attrid, ($kOrder + 1) * 10);
                }
                $phpAdoc->Set("sattr", implode(",", $attrids));
                $phpAdoc->Set("sAbsoluteOrders", implode(",", $tAbsOrders));
                $phpAdoc->SetBlockData("MATTR", $tmenu);
                $phpAdoc->SetBlockData("FATTR", $tfield);
                $phpAdoc->SetBlockData("AATTR", $taction);
                $phpAdoc->SetBlockData("NATTR", $tnormal);
                $phpAdoc->SetBlockData("ATTRFIELD", $tattr);

                $phpAdoc->set("hasattr", (count($tattr) > 0));
                $phpAdoc->SetBlockData("ACALC", $tcattr);
            } catch (Exception $e) {
                throw new Exception(sprintf("Structure \"%s\" : %s", $tdoc["name"], $e->getMessage()));
            }
        } else {
            $phpAdoc->Set("sAbsoluteOrders", "");
        }

        $phpAdoc->Set("STARMETHOD", false);
        if ($tdoc["name"] == '') {
            $tdoc["name"] = 'F__' . $tdoc["id"];
        }
        if ($tdoc["name"] != "") { // create name alias classes
            $phpAdoc->SetBlockData("CLASSALIAS", array(
                array(
                    "zou"
                )
            ));
            $phpAdoc->Set("docName", $tdoc["name"]);

            $phpAdoc->Set("SmartClass", str_replace("\\", "\\\\", SEManager::getFamilyClassName($tdoc["name"])));
            $phpAdoc->Set("PHPclassName", self::baseClassName(SEManager::getFamilyClassName($tdoc["name"])));
            $phpAdoc->Set("AdocClassName", self::baseClassName(SEManager::getAttributesClassName($tdoc["name"])));
        }
        $phpAdoc->Set("docTitle", str_replace('"', '\\"', $tdoc["title"]));
        $phpAdoc->set("HOOKALIAS", "");
        //----------------------------------
        // Add specials methods
        $cmethod = ""; // method file which is use as inherit virtual class
        $contents = '';
        $contents2 = '';
        $hasMethod = false;
        if (isset($tdoc["methods"]) && ($tdoc["methods"] != "")) {
            $tfmethods = explode("\n", $tdoc["methods"]);
            foreach ($tfmethods as $fmethods) {
                if ($fmethods[0] == "*") {
                    $cmethod = substr($fmethods, 1);
                    $filename = DEFAULT_PUBDIR . "/Apps/FDL/" . $cmethod;
                    $contents2 = self::getMethodFileInnerContents($filename);
                    /* Skip empty method file */
                    if (strlen(trim($contents2)) <= 0) {
                        $cmethod = '';
                        $contents2 = '';
                    }
                } else {
                    $filename = DEFAULT_PUBDIR . "/Apps/FDL/" . $fmethods;
                    $innerContents = self::getMethodFileInnerContents($filename);
                    /* Concatenate non-empty method file */
                    if (strlen(trim($innerContents)) > 0) {
                        $contents .= $innerContents;
                        $hasMethod = true;
                    }
                }
            }
        }
        if ($hasMethod) {
            $phpAdoc->Set("METHODS", $contents);
            $phpMethodName = sprintf("_Method_%s", $tdoc["name"]);
            $phpAdoc->set("PHPmethodName", $phpMethodName);
            $phpAdoc->set("ClassDocParent", $phpAdoc->Get("DocParent"));
            $phpAdoc->set("DocParent", '\\' . $phpMethodName);
        } else {
            $phpAdoc->Set("METHODS", "");
        }

        if ($cmethod != "") {
            $phpAdoc->Set("METHODS2", $contents2);
            $phpAdoc->Set("STARMETHOD", true);
            $phpAdoc->Set("docNameIndirect", '_SMethod_Doc' . $tdoc["id"] . "__");
            if ($hasMethod) {
                $phpAdoc->Set("RedirectDocParent", $phpAdoc->Get("ClassDocParent"));
                $phpAdoc->Set("ClassDocParent", '\\' . $phpAdoc->Get("docNameIndirect"));
            } else {
                $phpAdoc->Set("RedirectDocParent", $phpAdoc->Get("DocParent"));
                $phpAdoc->Set("DocParent", '\\' . $phpAdoc->Get("docNameIndirect"));
            }
        }
        $phpAdoc->Set("hasMethods", !empty($tdoc["methods"]));

        $dfiles["/vendor/Anakeen/Core/Layout/Class.NSSmart.layout"] = SEManager::getDocumentClassFilename($tdoc["docFile"]);
        $dfiles["/vendor/Anakeen/Core/Layout/Class.NSSmartAttr.layout"] = SEManager::getAttributesClassFilename($tdoc["docFile"]);
        $dfiles["/vendor/Anakeen/Core/Layout/Class.Doc.layout"] = sprintf("%s/Smart%d.php", $genDir, $tdoc["id"]);

        if (!empty($tdoc["methods"])) {
            $dfiles["/vendor/Anakeen/Core/Layout/Class.SmartMethods.layout"] = sprintf(
                "%s/Method.%s.php",
                $genDir,
                $tdoc["name"]
            );
        }

        foreach ($dfiles as $kFile => $dfile) {
            $phpAdoc->template = file_get_contents(DEFAULT_PUBDIR . $kFile);
            $err = self::__phpLintWriteFile($dfile, $phpAdoc->gen());

            if ($err != '') {
                throw new \Anakeen\Exception("CORE0023", $dfile, $err);
            }
        }
    }

    protected static function getDoctitleAttr(DocAttr $attr, $idDocTitle)
    {
        $doctitleAttr = clone($attr);
        $doctitleAttr->id = $idDocTitle;
        $doctitleAttr->type = "text";
        $doctitleAttr->accessibility = FieldAccessManager::getTextAccess(BasicAttribute::READ_ACCESS);
        $doctitleAttr->phpfile = "";
        $doctitleAttr->phpfunc = "";
        $doctitleAttr->options = "autotitle=yes|relativeOrder=" . $attr->id;
        $doctitleAttr->title = "N";
        $doctitleAttr->abstract = "N";
        $doctitleAttr->needed = "N";
        $doctitleAttr->usefor = "A";
        $doctitleAttr->link = "";
        $doctitleAttr->props = "";
        $doctitleAttr->phpconstraint = "";
        $doctitleAttr->labeltext = $attr->labeltext . ' ' . _("(title)");
        $doctitleAttr->ordered = $attr->ordered + 1;
        return $doctitleAttr;
    }
    protected static function attrIdToPhp($dbaccess, $tdoc)
    {
        $phpAdoc = new \Anakeen\Layout\TextLayout("vendor/Anakeen/Core/Layout/Class.Attrid.layout");

        if ($tdoc["fromid"] == 0) {
            $phpAdoc->Set("extend", '');
        } else {
            $fromName = \Anakeen\Core\SEManager::getNameFromId($tdoc["fromid"]);
            if ($fromName == '') {
                throw new \Anakeen\Exception("FAM0602", $tdoc["fromid"], $tdoc["name"]);
            }
            $phpAdoc->Set("extend", ucwords(strtolower(str_replace(array(
                ":",
                "-"
            ), "_", $fromName))));
        }

        $phpAdoc->Set("fromid", $tdoc["fromid"]);
        $phpAdoc->Set("title", $tdoc["title"]);
        $phpAdoc->Set("className", ucfirst(strtolower(str_replace(array(
            ":",
            "-"
        ), "_", $tdoc["name"]))));

        $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, DocAttr::class);
        $query->AddQuery(sprintf("docid=%d", $tdoc["id"]));
        $query->AddQuery(sprintf("id !~ ':'"));
        $query->order_by = "ordered";
        $attrs = $query->Query(0, 0, "TABLE");

        if ($query->nb > 0) {
            $const = array();
            foreach ($attrs as $attr) {
                $const[$attr["id"]] = array(
                    "attrid" => $attr["id"],
                    "type" => $attr["type"],
                    "label" => $attr["labeltext"],
                    "famName" => $tdoc["name"]
                );
            }

            $phpAdoc->SetBlockData("CONST", $const);
        }

        return $phpAdoc->gen();
    }

    protected static function baseClassName($class)
    {
        $part = explode('\\', $class);
        return array_pop($part);
    }

    protected static function doubleslash($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('"', '\\"', $s);
        return $s;
    }

    protected static function pgUpdateFamily($dbaccess, $docid, $docname = "")
    {
        $docname = strtolower($docname);
        $msg = '';
        /* Create family's table if not exists */
        if (!self::tableExists("public", "doc$docid")) {
            $msg .= sprintf("Create table 'doc%d'\n", $docid);
            self::createFamilyTable($docid);

            if (!self::tableExists("public", "doc$docid")) {
                $msg .= sprintf("Could not create table 'doc%d'.\n", $docid);
            }
        }

        $pgatt = self::getTableColumns("public", "doc$docid");
        // -----------------------------
        // add column attribute
        $qattr = new \Anakeen\Core\Internal\QueryDb($dbaccess, DocAttr::class);
        $qattr->AddQuery("docid=" . $docid);
        $qattr->AddQuery("type != 'menu'");
        $qattr->AddQuery("type != 'frame'");
        $qattr->AddQuery("type != 'tab'");
        $qattr->AddQuery("type != 'action'");
        $qattr->AddQuery("id !~ '^:'");
        //  $qattr->AddQuery("type !~ '^array'"); // must be visible to know for child attributes

        $qattr->AddQuery("usefor != 'Q' or usefor is null");

        $oattr = $qattr->Query();
        /**
         * @var DocAttr[] $tattr
         */
        $tattr = array();
        if ($qattr->nb > 0) {
            /**
             * @var DocAttr $attr
             */
            foreach ($oattr as $ka => $attr) {
                $tattr[strtolower($attr->id)] = $attr;
                $type = trim(strtok($attr->type, "("));
                if ($type === "docid" || $type === "account" || $type === "thesaurus") {
                    if ($attr->usefor !== "Q" && preg_match("/doctitle=([A-Za-z0-9_-]+)/", $attr->options, $reg)) {
                        $doctitle = $reg[1];
                        if ($doctitle == "auto") {
                            $doctitle = $attr->id . "_title";
                        }
                        $doctitle = strtolower($doctitle);
                        $tattr[$doctitle] = self::getDoctitleAttr($attr, $doctitle);
                    }
                }
            }

            foreach ($tattr as $ka => $attr) {
                $attr->id = chop($attr->id);
                if (substr($attr->type, 0, 5) == "array") {
                    continue;
                } // skip array but must be in table to search element in arrays
                if ($attr->docid == $docid) { // modify my field not inherited fields
                    if (!in_array($ka, $pgatt)) {
                        $msg .= "add field $ka in table doc" . $docid . "\n";
                        $repeat = (strpos($attr->options, "multiple=yes") !== false);
                        if (!$repeat) {
                            $repeat = (isset($tattr[$attr->frameid]) && $tattr[$attr->frameid]->type == "array");
                        }

                        $rtype = strtok($attr->type, "(");
                        switch ($rtype) {
                            case 'double':
                            case 'float':
                            case 'money':
                                $sqltype = " float8";
                                break;

                            case 'int':
                            case 'integer':
                                $sqltype = " int4";
                                break;

                            case 'date':
                                $sqltype = " date";
                                break;

                            case 'timestamp':
                                $sqltype = " timestamp without time zone";
                                break;

                            case 'time':
                                $sqltype = " time";
                                break;

                            case 'tsvector':
                                $sqltype = " tsvector";
                                break;

                            case 'xml':
                                $sqltype = "xml";
                                break;

                            case 'json':
                                $sqltype = "jsonb";
                                break;
                            default:
                                $sqltype = " text";
                        }
                        if ($repeat) {
                            $sqltype .= '[]';
                        }

                        self::alterTableAddColumn("public", "doc$docid", $ka, $sqltype);
                    }
                }
            }
        }
        /* Update family's view  */
        self::recreateFamilyView($docname, $docid);
        return $msg;
    }

    protected static function tableExists($schemaName, $tableName)
    {
        DbManager::query(
            sprintf(
                "SELECT 'true' FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                pg_escape_literal($schemaName),
                pg_escape_literal($tableName)
            ),
            $res,
            true,
            true
        );
        return ($res == 'true');
    }

    protected static function viewExists($schemaName, $viewName)
    {
        DbManager::query(
            sprintf(
                "SELECT 'true' FROM information_schema.views WHERE table_schema = %s AND table_name = %s",
                pg_escape_literal($schemaName),
                pg_escape_literal($viewName)
            ),
            $res,
            true,
            true
        );
        return ($res == 'true');
    }

    protected static function createFamilyTable($docid)
    {
        // create postgres table if new \familly
        $cdoc = SEManager::createTemporaryDocument($docid, false);
        $triggers = $cdoc->sqltrigger(false, true);
        $cdoc->query($triggers, 1);
        // step by step
        $cdoc->create();
        self::setSqlIndex($docid);
    }

    protected static function recreateFamilyView($docname, $docid)
    {
        DbManager::query(sprintf(
            "SELECT refreshFamilySchemaViews(%s, %s)",
            pg_escape_literal($docname),
            pg_escape_literal(intval($docid))
        ), $res, true, true);
    }

    protected static function getTableColumns($schemaName, $tableName)
    {
        DbManager::query(sprintf(
            "SELECT column_name FROM information_schema.columns WHERE table_schema = %s AND table_name = %s",
            pg_escape_literal($schemaName),
            pg_escape_literal($tableName)
        ), $res, true, false);
        return $res;
    }

    protected static function alterTableAddColumn($schemaName, $tableName, $columnName, $columnType)
    {
        DbManager::query(sprintf(
            "ALTER TABLE %s.%s ADD COLUMN %s %s",
            pg_escape_identifier($schemaName),
            pg_escape_identifier($tableName),
            pg_escape_identifier($columnName),
            $columnType
        ), $res, true, true);
    }

    public static function createDocFile($dbaccess, $tdoc)
    {
        $genDir = sprintf("%s/%s/SmartStructure/", DEFAULT_PUBDIR, Settings::DocumentGenDirectory);
        $genAttrDir = sprintf("%s/Fields", $genDir);
        $dfile = sprintf("%s/%s.php", $genDir, ucfirst(strtolower($tdoc["name"])));

        if (!is_dir($genDir)) {
            if (!(is_dir(dirname($genDir)))) {
                mkdir(dirname($genDir));
            }
            mkdir($genDir);
            mkdir($genAttrDir);
        }

        self::generateFamilyPhpClass($genDir, $tdoc);

        $attrfile = sprintf("%s/%s.php", $genAttrDir, ucfirst(strtolower($tdoc["name"])));

        $err = self::__phpLintWriteFile($attrfile, self::AttrIdtoPhp($dbaccess, $tdoc));
        if ($err != '') {
            throw new \Anakeen\Exception("CORE0024", $attrfile, $err);
        }

        return $dfile;
    }


    public static function deleteGenFiles($famName)
    {
        $files = [
            \Anakeen\Core\SEManager::getAttributesClassFilename($famName),
            \Anakeen\Core\SEManager::getDocumentClassFilename($famName)
        ];

        foreach ($files as $fdlgen) {
            if (file_exists($fdlgen) && is_file($fdlgen)) {
                if (!unlink($fdlgen)) {
                    throw new Exception("Could not delete file '%s'.", $fdlgen);
                }
            }
        }
    }

    public static function activateTrigger($docid)
    {
        $cdoc = SEManager::createTemporaryDocument($docid, false);
        $cdoc->query($cdoc->sqltrigger(false, true), 1);
        $sqlcmds = explode(";", $cdoc->SqlTrigger());
        //$cdoc = new_Doc($dbacceanss, $docid);
        //  print $cdoc->SqlTrigger();
        foreach ($sqlcmds as $k => $sqlquery) {
            if ($sqlquery != "") {
                $cdoc->query($sqlquery, 1);
            }
        }
    }

    public static function setSqlIndex($docid)
    {
        $cdoc = SEManager::createTemporaryDocument($docid, false);
        $indexes = $cdoc->GetSqlIndex();
        $msg = '';
        if ($indexes) {
            foreach ($indexes as $sqlIndex) {
                $msg .= $cdoc->query($sqlIndex);
            }
        }
        return $msg;
    }

    /**
     * refresh PHP Class & Postgres Table Definition
     *
     * @param string $dbaccess
     * @param int $docid
     *
     * @return string error message
     */
    public static function refreshPhpPgDoc($dbaccess, $docid)
    {
        $err = '';
        $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, \Anakeen\Core\SmartStructure::class);
        $query->AddQuery("doctype='C'");
        $query->AddQuery("id=$docid");
        $table1 = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $v = $table1[0];
            $err = self::buildFamilyFilesAndTables($dbaccess, $v, false);
        }

        return $err;
    }

    public static function buildFamilyFilesAndTables($dbaccess, $familyData, $interactive = false)
    {
        $locked = false;
        $savepointed = false;
        try {
            DbManager::setMasterLock(true);
            $locked = true;
            DbManager::savePoint(__METHOD__);
            $savepointed = true;

            $phpfile = self::createDocFile($dbaccess, $familyData);
            if ($interactive) {
                print "$phpfile [" . $familyData["title"] . "(" . $familyData["name"] . ")]\n";
            }
            $msg = self::pgUpdateFamily($dbaccess, $familyData["id"], $familyData["name"]);
            if ($interactive) {
                print $msg;
            } else {
                LogManager::notice($msg);
            }
            self::activateTrigger($familyData["id"]);

            DbManager::commitPoint(__METHOD__);
            $savepointed = false;
            DbManager::setMasterLock(false);
        } catch (\Exception $e) {
            if ($savepointed) {
                DbManager::rollbackPoint(__METHOD__);
            }
            if ($locked) {
                DbManager::setMasterLock(false);
            }
            return $e->getMessage();
        }
        return '';
    }


    /**
     * complete attribute properties from  parent attribute
     *
     * @param string $dbaccess
     * @param DocAttr $ta
     *
     * @return mixed
     */
    protected static function completeAttribute($dbaccess, $ta)
    {
        $ta->id = substr($ta->id, 1);
        $fromid = MiscDoc::getFamFromId($ta->docid);
        $tfromid[] = $fromid;
        while ($fromid = MiscDoc::getFamFromId($fromid)) {
            $tfromid[] = $fromid;
        }
        $tfromid[] = $ta->docid; // itself
        $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, DocAttr::class);
        $query->AddQuery(DbManager::getSqlOrCond($tfromid, 'docid'));
        $query->AddQuery("id='" . pg_escape_string($ta->id) . "'");
        $query->order_by = "docid";
        $tas = $query->Query(0, 0, "TABLE");

        if ($query->nb == 0) {
            error_log("MODATTR error for " . $ta->id);
            return $ta;
        } else {
            $tw = $ta;

            foreach ($tas as $ta1) {
                if (preg_match("/(.*)relativeOrder=([A-Za-z0-9_:-]+)(.*)/", $ta->options, $attrReg)) {
                    if (preg_match("/(.*)relativeOrder=([A-Za-z0-9_:-]+)(.*)/", $ta1["options"], $parentReg)) {
                        // Special case to copy parent options when relativeOrder is used
                        if (($parentReg[1] || $parentReg[3]) && (!$attrReg[1] && !$attrReg[3])) {
                            // Copy on if no explicit option is set
                            $tw->options = sprintf("%srelativeOrder=%s%s", $parentReg[1], $attrReg[2], $parentReg[3]);
                        }
                    }
                }
                foreach ($ta1 as $k => $v) {
                    if ($v && (!$ta->$k)) {
                        $tw->$k = $v;
                    }
                    if ($ta->$k == "-") {
                        $tw->$k = "";
                    } // suppress value
                }
            }

            return $tw;
        }
    }

    /**
     * get parent attributes
     *
     * @param string $dbaccess
     * @param string $fromid
     *
     * @return array
     */
    protected static function getParentAttributes($dbaccess, $fromid)
    {
        if ($fromid > 0) {
            $query = new \Anakeen\Core\Internal\QueryDb($dbaccess, DocAttr::class);
            $query->AddQuery(sprintf("docid=%d", $fromid));

            $pa = $query->Query(0, 0, "TABLE");
            if (!$pa) {
                $pa = [];
            }

            $nextfromid = MiscDoc::getFamFromId($fromid);
            if ($nextfromid > 0) {
                $pa = array_merge(self::getParentAttributes($dbaccess, $nextfromid), $pa);
            }
            $paf = array();
            foreach ($pa as $v) {
                $paf[$v["id"]] = $v;
                if (preg_match("/^(docid|account)/", $v["type"])) {
                    if (preg_match('/\bdoctitle=(?P<attrid>[A-Za-z0-9_-]+)\b/', $v["options"], $m)) {
                        $vtitle = $v;
                        if ($m['attrid'] == 'auto') {
                            $vtitle["id"] = $v["id"] . "_title";
                        } else {
                            $vtitle["id"] = strtolower($m['attrid']);
                        }
                        $vtitle["type"] = "text";
                        $vtitle["options"] = "relativeOrder=" . $v["id"];
                        $paf[$vtitle["id"]] = $vtitle;
                    }
                }
            }
            return $paf;
        }
        return array();
    }

    /**
     * Extract the main type and the format from a type string
     *
     * @param string $type e.g. 'array("empty")'
     *
     * @return array() struct e.g. array('type' => 'array', 'format' => '"empty"')
     */
    public static function parseType($type)
    {
        if (preg_match('/^\s*(?P<type>[a-z]+)(?P<format>\(.+\))?\s*$/i', $type, $m)) {
            /* Remove leading and trailing parenthesis from format */
            if (empty($m['format'])) {
                $m['format'] = '';
            }
            $m['format'] = substr($m['format'], 1, -1);
            return array(
                'type' => $m['type'],
                'format' => $m['format']
            );
        }
        return array(
            'type' => $type,
            'format' => ''
        );
    }

    protected static function getTypeMain($type)
    {
        $p = self::parseType($type);
        return $p['type'];
    }

    protected static function getTypeFormat($type)
    {
        $p = self::parseType($type);
        return $p['format'];
    }

    /**
     * Get the content of a METHOD file without the PHP opening/closing tags and
     * without the @begin-method-ignore/@end-method-ignore sections.
     *
     * @param $filename
     *
     * @return string
     */
    protected static function getMethodFileInnerContents($filename)
    {
        $contents = file_get_contents($filename);
        if ($contents === false) {
            return '';
        }
        $contents = preg_replace(
            '%(?:  //[^\n]*@begin-method-ignore|  /\*+[^/]*?@begin-method-ignore)(.*?)(?:  //[^\n]* @end-method-ignore[^\n]*|  /\*+[^/]*?@end-method-ignore[^/]*?\*/)%xms',
            '',
            $contents
        );
        $contents = str_replace(array(
            "<?php\n",
            "<?php\r\n",
            "\n?>"
        ), "", $contents);
        return (string)$contents;
    }
}
