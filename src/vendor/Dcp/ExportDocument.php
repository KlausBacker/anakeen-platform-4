<?php

namespace Dcp;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\FieldAccessManager;

class ExportDocument
{
    const useAclDocumentType = ":useDocument";
    const useAclAccountType = ":useAccount";

    protected $alreadyExported = array();
    protected $lattr;
    protected $prevfromid = -1;
    protected $familyName = '';
    protected $csvEnclosure = '"';
    protected $csvSeparator = ';';
    protected $encoding = 'utf-8';
    protected $verifyAttributeAccess = false;
    protected $attributeGrants = array();
    protected $noAccessText = \Anakeen\Core\Internal\FormatCollection::noAccessText;
    protected $exportAccountType = self::useAclAccountType;

    private $logicalName = [];

    private $logins = [];

    /**
     * Use when cannot access attribut value
     * Due to visibility "I"
     *
     * @param string $noAccessText
     */
    public function setNoAccessText($noAccessText)
    {
        $this->noAccessText = $noAccessText;
    }

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
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @param string $csvSeparator
     */
    public function setCsvSeparator($csvSeparator)
    {
        $this->csvSeparator = $csvSeparator;
    }

    /**
     * @param string $csvEnclosure
     */
    public function setCsvEnclosure($csvEnclosure)
    {
        $this->csvEnclosure = $csvEnclosure;
    }

    public function reset()
    {
        $this->alreadyExported = array();
    }

    /**
     * @return array
     */
    public function getTrans()
    {
        static $htmlTransMapping = false;
        if (!$htmlTransMapping) {
            // to invert HTML entities
            $htmlTransMapping = get_html_translation_table(HTML_ENTITIES);
            $htmlTransMapping = array_flip($htmlTransMapping);
            $htmlTransMapping = array_map("utf8_encode", $htmlTransMapping);
        }
        return $htmlTransMapping;
    }

    protected function getUserLogin($uid)
    {
        if (!isset($this->logins[$uid])) {
            simpleQuery("", sprintf("select login from users where id=%d", $uid), $login, true, true);
            $this->logins[$uid] = $login ? $login : 0;
        }
        return $this->logins[$uid];
    }

    protected function getUserLogicalName($uid)
    {
        if (!isset($this->logicalName[$uid])) {
            simpleQuery("", sprintf("select name from docread where id=(select fid from users where id = %d)", $uid), $logicalName, true, true);
            $this->logicalName[$uid] = $logicalName ? $logicalName : 0;
        }
        return $this->logicalName[$uid];
    }

    /**
     * @param resource   $fout
     * @param string|int $docid
     */
    public function exportProfil($fout, $docid)
    {
        if (!$docid) {
            return;
        }
        // import its profile
        $doc = \new_Doc("", $docid); // needed to have special acls
        $doc->acls[] = "viewacl";
        $doc->acls[] = "modifyacl";
        if ($doc->name != "") {
            $name = $doc->name;
        } else {
            $name = $doc->id;
        }

        $dbaccess = DbManager::getDbAccess();
        $q = new \Anakeen\Core\Internal\QueryDb($dbaccess, \DocPerm::class);
        $q->AddQuery(sprintf("docid=%d", $doc->profid));
        $q->order_by = "userid";
        $acls = $q->Query(0, 0, "TABLE");

        $tAcls = array();
        if ($acls) {
            foreach ($acls as $va) {
                $up = $va["upacl"];
                $uid = $va["userid"];

                if ($uid >= \VGroup::STARTIDVGROUP) {
                    $qvg = new \Anakeen\Core\Internal\QueryDb($dbaccess, \VGroup::class);
                    $qvg->AddQuery(sprintf("num=%d", $uid));
                    $tvu = $qvg->Query(0, 1, "TABLE");
                    $uid = sprintf("attribute(%s)", $tvu[0]["id"]);
                } else {
                    if ($this->exportAccountType === self::useAclDocumentType) {
                        $uln = $this->getUserLogicalName($uid);
                        if ($uln) {
                            if (preg_match('/^attribute\(.*\)$/', $uln)) {
                                $uid = sprintf("document(%s)", $uln);
                            } else {
                                $uid = $uln;
                            }
                        } else {
                            $uid = $this->getUserLogin($uid);
                            if ($uid) {
                                $uid = sprintf("account(%s)", $uid);
                            }
                        }
                    } else {
                        $uid = $this->getUserLogin($uid);
                        if (preg_match('/^attribute\(.*\)$/', $uid)) {
                            $uid = sprintf("account(%s)", $uid);
                        }
                    }
                }
                foreach ($doc->acls as $kAcl => $acl) {
                    $bup = ($doc->accessControl()->controlUp($up, $acl) == "");
                    if ($uid && $bup) {
                        $tAcls[$kAcl . "-" . $uid] = ["uid" => $uid, "acl" => $acl];
                    }
                }
            }
        }
        // add extended Acls
        if ($doc->extendedAcls) {
            $extAcls = array_keys($doc->extendedAcls);
            $aclCond = \Anakeen\Core\DbManager::getSqlOrCond($extAcls, "acl");
            simpleQuery($dbaccess, sprintf("select * from docpermext where docid=%d and %s order by userid", $doc->profid, $aclCond), $eAcls);

            foreach ($eAcls as $kAcl => $aAcl) {
                $uid = $aAcl["userid"];
                if ($uid >= \VGroup::STARTIDVGROUP) {
                    $qvg = new \Anakeen\Core\Internal\QueryDb($dbaccess, \VGroup::class);
                    $qvg->AddQuery(sprintf("num=%d", $uid));
                    $tvu = $qvg->Query(0, 1, "TABLE");
                    $uid = sprintf("attribute(%s)", $tvu[0]["id"]);
                } else {
                    $uid = $this->getUserLogin($uid);
                    if (preg_match('/^attribute\(.*\)$/', $uid)) {
                        $uid = sprintf("account(%s)", $uid);
                    }
                }
                if ($uid) {
                    $tAcls["e" . $kAcl . "-" . $uid] = ["uid" => $uid, "acl" => $aAcl["acl"]];
                }
            }
        }
        if (count($tAcls) > 0) {
            $data = array(
                "PROFIL",
                $name,
                $this->exportAccountType,
                ""
            );
            ksort($tAcls);
            foreach ($tAcls as $ku => $oneAcl) {
                //fputs_utf8($fout, ";" . $tpa[$ku] . "=" . $uid);
                $data[] = sprintf("%s=%s", $oneAcl["acl"], $oneAcl["uid"]);
            }
            \Dcp\WriteCsv::fput($fout, $data);
        }
    }


    public function csvExport(\Anakeen\Core\Internal\SmartElement & $doc, &$ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat)
    {
        if (!$doc->isAffected()) {
            return;
        }
        if (in_array($doc->id, $this->alreadyExported)) {
            return;
        }
        $this->alreadyExported[] = $doc->id;

        \Dcp\WriteCsv::$separator = $this->csvSeparator;
        \Dcp\WriteCsv::$enclosure = $this->csvEnclosure;
        \Dcp\WriteCsv::$encoding = ($wutf8) ? "utf-8" : "iso8859-15";

        $efldid = '';
        $dbaccess = $doc->dbaccess;
        if ($this->prevfromid != $doc->fromid) {
            if (($eformat != "I") && ($this->prevfromid > 0)) {
                \Dcp\WriteCsv::fput($fout, array());
            }
            $adoc = $doc->getFamilyDocument();
            if ($adoc->name != "") {
                $this->familyName = $adoc->name;
            } else {
                $this->familyName = $adoc->id;
            }
            if (!$this->familyName) {
                return;
            }
            $this->lattr = $adoc->GetExportAttributes($wfile, $nopref);
            $data = array();

            if ($eformat == "I") {
                $data = array(
                    "//FAM",
                    $adoc->title . "(" . $this->familyName . ")",
                    "<specid>",
                    "<fldid>"
                );
                //fputs_utf8($fout, "//FAM;" . $adoc->title . "(" . $this->familyName . ");<specid>;<fldid>;");
            }
            foreach ($this->lattr as $attr) {
                $data[] = $attr->getLabel();
                //fputs_utf8($fout, str_replace(SEPCHAR, ALTSEPCHAR, $attr->getLabel()) . SEPCHAR);
            }
            WriteCsv::fput($fout, $data);
            //fputs_utf8($fout, "\n");
            if ($eformat == "I") {
                $data = array(
                    "ORDER",
                    $this->familyName,
                    "",
                    ""
                );
                //fputs_utf8($fout, "ORDER;" . $this->familyName . ";;;");
                foreach ($this->lattr as $attr) {
                    $data[] = $attr->id;
                    //fputs_utf8($fout, $attr->id . ";");
                }
                WriteCsv::fput($fout, $data);
                // fputs_utf8($fout, "\n");
            }
            $this->prevfromid = $doc->fromid;
        }
        $docName = '';
        if ($doc->name != "" && $doc->locked != -1) {
            $docName = $doc->name;
        } elseif ($wprof) {
            if ($doc->locked != -1) {
                $err = $doc->setNameAuto(true);
                $docName = $doc->name;
            }
        } elseif ($wident) {
            $docName = $doc->id;
        }
        $data = array();
        if ($eformat == "I") {
            $data = array(
                "DOC",
                $this->familyName,
                $docName,
                $efldid
            );
        }
        // write values
        foreach ($this->lattr as $attr) {
            if ($this->verifyAttributeAccess && !FieldAccessManager::hasReadAccess($doc, $attr)) {
                $data[] = $this->noAccessText;
                continue;
            }
            if ($attr->isMultiple()) {
                $rawValue = $doc->getMultipleRawValues($attr->id);
            } else {
                $rawValue = $doc->getRawValue($attr->id);
            }
            if ($eformat == 'F') {
                if ($this->csvEnclosure) {
                    $csvValue = str_replace(array(
                        \ImportSingleDocument::CSVSECONDLEVELMULTIPLE,
                        '<br/>'
                    ), array(
                        "\n",
                        "\\n"
                    ), $doc->getHtmlAttrValue($attr->id, '', false, -1, false));
                } else {
                    $csvValue = str_replace(array(
                        \ImportSingleDocument::CSVSECONDLEVELMULTIPLE,
                        '<br/>'
                    ), '\\n', $doc->getHtmlAttrValue($attr->id, '', false, -1, false));
                }
            } else {
                $csvValue = $rawValue;
            }
            if ($attr->type === "longtext" && is_array($csvValue)) {
                foreach ($csvValue as $ck => $singleValue) {
                    $csvValue[$ck] = str_replace("\n", \ImportSingleDocument::CSVLONGTEXTMULTIPLE, $singleValue);
                }
            }

            // invert HTML entities
            if (($attr->type == "image") || ($attr->type == "file")) {
                $tfiles = $doc->vault_properties($attr);
                $tf = array();
                foreach ($tfiles as $f) {
                    $ldir = $doc->id . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', \Anakeen\Core\Utils\Strings::Unaccent($doc->title)) . "_D";
                    $fname = $ldir . '/' . \Anakeen\Core\Utils\Strings::Unaccent($f["name"]);
                    $tf[] = $fname;
                    $ef[$fname] = array(
                        "path" => $f["path"],
                        "ldir" => $ldir,
                        "fname" => \Anakeen\Core\Utils\Strings::Unaccent($f["name"])
                    );
                }
                $csvValue = implode("\n", $tf);
            } elseif ($attr->type == "docid" || $attr->type == "account" || $attr->type == "thesaurus") {
                $docrevOption = $attr->getOption("docrev", "latest");
                if ($eformat !== 'F' && $rawValue != "") {
                    if (is_array($rawValue)) {
                        $csvValue = array();
                        foreach ($rawValue as $did) {
                            $tnbr = array();
                            if (!is_array($did)) {
                                $brtid = [$did];
                            } else {
                                $brtid = $did;
                            }

                            foreach ($brtid as $brid) {
                                $n = \Anakeen\Core\SEManager::getNameFromId($brid);
                                if ($n) {
                                    if ($docrevOption === "latest") {
                                        $tnbr[] = $n;
                                    } else {
                                        \Anakeen\Core\Utils\System::addWarningMsg(
                                            sprintf(
                                                _("Doc %s : Attribut \"%s\" reference revised identifier : cannot use logical name"),
                                                $doc->getTitle(),
                                                $attr->getLabel()
                                            )
                                        );
                                        $tnbr[] = $brid;
                                    }
                                } else {
                                    $tnbr[] = $brid;
                                }
                            }
                            $csvValue[] = implode(\ImportSingleDocument::CSVSECONDLEVELMULTIPLE, $tnbr);
                        }
                    } else {
                        $n = \Anakeen\Core\SEManager::getNameFromId($csvValue);
                        if ($n) {
                            if ($docrevOption === "latest") {
                                $csvValue = $n;
                            } else {
                                \Anakeen\Core\Utils\System::addWarningMsg(
                                    sprintf(
                                        _("Doc %s : Attribut \"%s\" reference revised identifier : cannot use logical name"),
                                        $doc->getTitle(),
                                        $attr->getLabel()
                                    )
                                );
                            }
                        }
                    }
                }
            } elseif ($attr->type == "htmltext") {
                $csvValue = $attr->prepareHtmltextForExport(WriteCsv::flatValue($csvValue));
                if ($wfile) {
                    $csvValue = preg_replace_callback(
                        '/(<img.*?src=")(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/',
                        function ($matches) use (&$ef) {
                            if (isset($matches[7])) {
                                $docid = $matches[8];
                                $attrid = $matches[9];
                                $index = $matches[10] == "-1" ? 0 : $matches[10];
                            } else {
                                $docid = $matches[4];
                                $index = $matches[6] == "-1" ? 0 : $matches[6];
                                $attrid = $matches[5];
                            }

                            $doc = SEManager::getDocument($docid);
                            if ($doc && $doc->hasPermission("view")) {
                                $attr = $doc->getAttribute($attrid);
                                $tfiles = $doc->vault_properties($attr);
                                $f = $tfiles[$index];

                                $ldir = $doc->id . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', \Anakeen\Core\Utils\Strings::Unaccent($doc->title)) . "_D";
                                $fname = $ldir . '/' . \Anakeen\Core\Utils\Strings::Unaccent($f["name"]);
                                $ef[$fname] = array(
                                    "path" => $f["path"],
                                    "ldir" => $ldir,
                                    "fname" => \Anakeen\Core\Utils\Strings::Unaccent($f["name"])
                                );
                                return $matches[1] . "file://" . $fname . '"';
                            }
                            return "";
                        },
                        $csvValue
                    );
                }
            } else {
                $trans = $this->getTrans();
                $csvValue = preg_replace_callback('/(\&[a-zA-Z0-9\#]+;)/s', function ($matches) use ($trans) {
                    return strtr($matches[1], $trans);
                }, $csvValue);
                // invert HTML entities which ascii code like &#232;
                $csvValue = preg_replace_callback('/\&#([0-9]+);/s', function ($matches) {
                    return chr($matches[1]);
                }, $csvValue);
            }

            $data[] = $csvValue;
        }

        \Dcp\WriteCsv::fput($fout, $data);
        if ($wprof) {
            $profid = ($doc->dprofid) ? $doc->dprofid : $doc->profid;
            if ($profid == $doc->id) {
                $this->exportProfil($fout, $doc->id);
            } elseif ($profid > 0) {
                $name = \Anakeen\Core\SEManager::getNameFromId($profid);
                $dname = $doc->name;
                if (!$dname) {
                    $dname = $doc->id;
                }
                if (!$name) {
                    $name = $profid;
                }
                if (!isset($tdoc[$profid])) {
                    $tdoc[$profid] = true;
                    $pdoc = \new_Doc($dbaccess, $profid);
                    $this->csvExport($pdoc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
                }
                $data = array(
                    "PROFIL",
                    $dname,
                    $name,
                    ""
                );
                \Dcp\WriteCsv::fput($fout, $data);
            }
        }
    }

    /**
     * @param string $exportAccountType
     *
     * @throws Exception
     */
    public function setExportAccountType($exportAccountType)
    {
        $availables = [self::useAclAccountType, self::useAclDocumentType];
        if (!in_array($exportAccountType, $availables)) {
            throw new Exception("PRFL0300", $exportAccountType, implode(", ", $availables));
        }
        $this->exportAccountType = $exportAccountType;
    }
}
