<?php
namespace Dcp\Devel;

use Dcp\Exception;

class ExportFamily
{
    const OTHERTAGPREFIX = "FAMEXPORT:";
    protected $csvEnclosure = '"';
    protected $csvSeparator = ',';
    /**
     * @var \DocFam
     */
    protected $family = null;
    protected $data = [];
    protected $exportDocuments = [];
    protected $workDirectory;
    protected $contentDescription = [];
    /**
     * @var \Dcp\ExportDocument
     */
    protected $export;
    /**
     * @var \ZipArchive
     */
    protected $zip;
    protected $infoInstall = [];
    protected $infoUpgrade = [];

    /**
     * @param \DocFam $family
     *
     * @throws Exception
     */
    public function setFamily(\DocFam $family)
    {
        $this->family = $family;
        if ($this->family->fromid) {
            $this->family->fromname = getNameFromId("", $this->family->fromid);
        }
        $this->contentDescription['name'] = $this->family->name;
        $this->contentDescription['parent'] = $this->family->fromname;
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

    public function exportProfil($docid)
    {
    }

    public function export()
    {
        $filename = tempnam(getTmpDir(), "exportFam") . ".zip";
        $this->workDirectory = sprintf("%s/%s", getTmpDir(), uniqid("exportFam.d"));
        $this->export = new \Dcp\ExportDocument();
        $this->export->setCsvEnclosure($this->csvEnclosure);
        $this->export->setCsvSeparator($this->csvSeparator);

        if (!mkdir($this->workDirectory)) {
            throw new Exception(sprintf("Cannot create temporary directory \"%s\"", $this->workDirectory));
        }

        $this->zip = new \ZipArchive();
        $this->zip->open($filename, \ZipArchive::CREATE);
        // $documents = $this->getDocumentToExport();
        $this->exportStructure();
        $this->exportParam();
        $this->exportConfig();
        $this->exportWorkflow();
        $this->exportOthers();
        $this->exportInfoXml();
        $this->exportContentDescription();
        $this->zip->close();
        return $filename;
    }

    protected function exportContentDescription()
    {
        $filename = sprintf("%s/%s__DESC.json", $this->workDirectory, $this->family->name);
        file_put_contents($filename, json_encode($this->contentDescription, JSON_PRETTY_PRINT));
        $this->zip->addFile($filename, basename($filename));
    }

    protected function exportInfoXml()
    {
        $filename = sprintf("%s/%s__info.xml", $this->workDirectory, $this->family->name);
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        $root = $dom->createElement("module");
        $install = $dom->createElement("post-install");
        $upgrade = $dom->createElement("post-upgrade");
        if ($this->csvEnclosure === '"') {
            $enclosureArg = "'\"'";
        } elseif ($this->csvEnclosure === "'") {
            $enclosureArg = "\"'\"";
        } else {
            $enclosureArg = $this->csvEnclosure;
        }

        foreach ($this->infoInstall as $installFile) {
            $command = sprintf(
                './wsh.php --api=importDocuments --file=./@APPNAME@/%s --csv-separator="%s" --csv-enclosure=%s',
                $installFile,
                $this->csvSeparator,
                $enclosureArg
            );
            $process = $dom->createElement("process");
            $process->setAttribute("id", base64_encode("pi" . $installFile));
            $process->setAttribute("command", $command);
            $install->appendChild($process);
        }
        foreach ($this->infoUpgrade as $installFile) {
            $command = sprintf(
                './wsh.php --api=importDocuments --file=./@APPNAME@/%s --csv-separator="%s" --csv-enclosure=%s',
                $installFile,
                $this->csvSeparator,
                $enclosureArg
            );
            $process = $dom->createElement("process");
            $process->setAttribute("id", base64_encode("pu" . $installFile));
            $process->setAttribute("command", $command);
            $upgrade->appendChild($process);
        }

        $root->appendChild($install);
        $root->appendChild($upgrade);

        $dom->appendChild($root);

        file_put_contents($filename, $dom->saveXML());

        $this->zip->addFile($filename, basename($filename));
        $this->contentDescription['Infoxml'] = basename($filename);
    }

    protected function exportDocument(\Doc $configDocument, $fout)
    {
        $this->export->csvExport($configDocument, $file, $fout, true, false, false, true, true, "I");
    }

    /**
     * FAMNAME__PARAM.csv¶
     * BEGIN
     * PROFID
     * CVID
     * PARAM
     * INITIAL
     * DEFAULT
     */
    protected function exportParam()
    {
        $filename = sprintf("%s/%s__PARAM.csv", $this->workDirectory, $this->family->name);

        $data[] = ["//BEGIN", "Parent family", "Family title", "", "", "Family name"];
        $data[] = ["BEGIN", $this->family->fromname, "", "", "", $this->family->name];
        $fout = fopen($filename, "a");
        if ($this->family->profid) {
            $configDocument = new_Doc("", $this->family->profid);
            $data[] = ["PROFID", ($configDocument->name) ? $configDocument->name : $configDocument->id];
            if ($this->family->profid == $this->family->id) {
                $this->export->exportProfil($fout, $configDocument->id);
            } else {
                $this->exportDocument($configDocument, $fout);
            }
        }
        if ($this->family->cprofid) {
            $configDocument = new_Doc("", $this->family->cprofid);
            $data[] = ["CPROFID", ($configDocument->name) ? $configDocument->name : $configDocument->id];

            $this->exportDocument($configDocument, $fout);
        }
        if ($this->family->ccvid) {
            $configDocument = new_Doc("", $this->family->ccvid);
            $data[] = ["CVID", ($configDocument->name) ? $configDocument->name : $configDocument->id];

            $this->exportDocument($configDocument, $fout);
        }

        fclose($fout);

        $data = array_merge($data, $this->getStruct(true));

        $defaults = $this->family->getOwnDefValues();
        if ($defaults) {
            $data[] = ["//DEFAULT", "Id", "Default Value"];
            foreach ($defaults as $attrid => $defValue) {
                $data[] = ["DEFAULT", $attrid, $defValue];
            }
        }

        $params = $this->family->getOwnParams();
        if ($params) {
            $data[] = ["//INITIAL", "Id", "Initial value"];
            foreach ($params as $attrid => $pValue) {
                $data[] = ["INITIAL", $attrid, $pValue];
            }
        }
        $data[] = ["END", "", "", "", "", ""];

        $this->putcsv($filename, $data);

        $this->sortData($filename);
        $this->zip->addFile($filename, basename($filename));
        $this->contentDescription['Param'] = basename($filename);
        $this->infoInstall[] = basename($filename);
    }

    /** FAMNAME__STRUCT.csv¶
     * BEGIN
     * PROFID
     * CVID
     * PARAM
     * INITIAL
     * DEFAULT
     */
    protected function exportStructure()
    {
        $filename = sprintf("%s/%s__STRUCT.csv", $this->workDirectory, $this->family->name);

        $data[] = ["//BEGIN", "Parent family", "Family title", "", "", "Family name"];
        $data[] = ["BEGIN", $this->family->fromname, "", "", "", $this->family->name];
        //$fout = fopen($filename, "a");
        $data = array_merge($data, $this->getStruct(false));
        $data[] = ["END", "", "", "", "", ""];

        $this->putcsv($filename, $data);

        $this->zip->addFile($filename, basename($filename));
        $this->contentDescription['Struct'] = basename($filename);
        $this->infoInstall[] = basename($filename);
        $this->infoUpgrade[] = basename($filename);
    }

    protected function getStruct($param = false)
    {
        //	idattr	idframe	label	T	A	type	ord	vis	need	link	phpfile	phpfunc	elink	constraint	option
        $sql = sprintf("select * from docattr where docid=%d", $this->family->id);
        if ($param) {
            $sql .= "and usefor = 'Q'";
            $key = "PARAM";
        } else {
            $sql .= "and usefor != 'Q'";
            $key = "ATTR";
        }
        $sql .= " order by ordered";

        simpleQuery("", $sql, $attrs);

        $data[] = [
            "//$key",
            "Id",
            "Parent",
            "Label",
            "isTitle",
            "isAbstract",
            "Type",
            "Order",
            "Visibility",
            "Needed",
            "Link",
            "PhpFile",
            "PhpFunc",
            "Elink",
            "Constraint",
            "Options"
        ];
        foreach ($attrs as $attr) {
            if (preg_match("/(\\|?)relativeOrder=([^\\|]*)/", $attr["options"], $reg)) {
                $attr["options"] = str_replace($reg[0], "", $attr["options"]);
                $attr["ordered"] = $reg[2];
            }
            if ($attr["id"][0] === ":" && !$param) {
                $key = "MODATTR";
                $attr["id"] = substr($attr["id"], 1);
            } elseif ($param) {
                $key = "PARAM";
            } else {
                $key = "ATTR";
            }

            if ($attr["type"] === "enum" && empty($attr["phpfile"])) {
                $attr["phpfunc"] = \EnumAttributeTools::getFlatEnumNotation($this->family->id, $attr["id"]);
            }
            
            $data[] = [
                $key,
                $attr["id"],
                $attr["frameid"],
                $attr["labeltext"],
                $attr["title"],
                $attr["abstract"],
                $attr["type"],
                $attr["ordered"],
                $attr["visibility"],
                $attr["needed"],
                $attr["link"],
                $attr["phpfile"],
                $attr["phpfunc"],
                $attr["elink"],
                $attr["phpconstraint"],
                $attr["options"]
            ];
        }

        return $data;
    }

    /* FAMNAME__CONFIG.csv

       BEGIN (Title)
       ICON
       DFLDID
       CFLDID
       SCHAR
       CLASS
       METHOD
       TAG
       USEFOR*/
    protected function exportConfig()
    {
        $data[] = ["//BEGIN", "Parent family", "Family title", "", "", "Family name"];
        $data[] = ["BEGIN", $this->family->fromname, $this->family->title, "", "", $this->family->name];

        $filename = sprintf("%s/%s__CONFIG.csv", $this->workDirectory, $this->family->name);

        $fout = fopen($filename, "a");
        if ($this->family->icon) {
            if (preg_match(PREGEXPFILE, $this->family->icon, $reg)) {
                $vid = $reg["vid"];
                $info = \Dcp\VaultManager::getFileInfo($vid);

                $data[] = ["ICON", $info->name];

                $this->zip->addFile($info->path, $info->name);
                $this->contentDescription['Icon'] = $info->name;
            } else {
                $data[] = ["ICON", $this->family->icon];

                $this->zip->addFile(sprintf("%s/Images/%s", DEFAULT_PUBDIR, $this->family->icon), $this->family->icon);
                $this->contentDescription['Icon'] = $this->family->icon;
            }
        }

        if ($this->family->schar) {
            $data[] = ["SCHAR", $this->family->schar];
        }

        if ($this->family->usefor) {
            $data[] = ["USEFOR", $this->family->usefor];
        }
        if ($this->family->methods) {
            $data[] = ["METHOD", $this->family->methods];
        }
        if ($this->family->classname) {
            $data[] = ["CLASS", $this->family->classname];
        }
        if ($this->family->atags) {
            $tags = explode("\n", $this->family->atags);
            foreach ($tags as $tag) {
                $data[] = ["TAGS", $tag];
            }
        }
        if ($this->family->dfldid) {
            $configDocument = new_Doc("", $this->family->dfldid);
            $data[] = ["DFLDID", ($configDocument->name) ? $configDocument->name : $configDocument->id];
            $this->exportDocument($configDocument, $fout);
        }
        if ($this->family->cfldid) {
            $configDocument = new_Doc("", $this->family->cfldid);
            $data[] = ["CFLDID", ($configDocument->name) ? $configDocument->name : $configDocument->id];
            $this->exportDocument($configDocument, $fout);
        }

        $data[] = ["END", "", "", "", "", ""];
        fclose($fout);

        $this->sortData($filename);
        $this->putcsv($filename, $data);

        $this->zip->addFile($filename, basename($filename));
        $this->contentDescription['Config'] = basename($filename);
        $this->infoInstall[] = basename($filename);
    }

    protected function exportWorkflow()
    {
        if ($this->family->wid) {
            $workflow = new_Doc("", $this->family->wid);
            $filename = sprintf("%s/%s__WORKFLOW.csv", $this->workDirectory, $this->family->name);

            $fout = fopen($filename, "a");
            $exportDocuments = $this->getDocumentToExport();
            foreach ($exportDocuments as $exportData) {
                if ($exportData["index"][0] === "w") {
                    $doc = new_Doc("", $exportData["docid"]);
                    if (!$doc->isAlive()) {
                        throw new Exception(
                            sprintf(
                                "Export Document %s (%s)not alive",
                                $exportData["docid"],
                                $exportData["index"]
                            )
                        );
                    }
                    $this->exportDocument($doc, $fout);
                }
            }
            fclose($fout);

            $data[] = ["//BEGIN", "Parent family", "Family title", "", "", "Family name"];
            $data[] = ["BEGIN", $this->family->fromname, "", "", "", $this->family->name];
            $data[] = ["WID", $workflow->name ? $workflow->name : $workflow->id];
            $data[] = ["END", "", "", "", "", ""];
            $this->putcsv($filename, $data);

            $this->sortData($filename);
            $this->zip->addFile($filename, basename($filename));
            $this->contentDescription['Workflow'] = basename($filename);
            $this->contentDescription['wfam'] = $workflow->name;
            $this->infoInstall[] = basename($filename);
        }
    }

    protected function exportOthers()
    {
        $exportDocuments = $this->getOtherDocumentToExport();

        if ($exportDocuments) {
            $atags = [];
            $filename = sprintf("%s/%s__OTHERS.csv", $this->workDirectory, $this->family->name);

            $fout = fopen($filename, "a");
            foreach ($exportDocuments as $exportData) {
                $doc = new_Doc("", $exportData["docid"]);
                if (!$doc->isAlive()) {
                    throw new Exception(
                        sprintf(
                            "Export Document %s (%s)not alive",
                            $exportData["docid"],
                            $exportData["index"]
                        )
                    );
                }

                $this->exportDocument($doc, $fout);
                $atags[] = array(
                    "DOCATAG",
                    $exportData["name"] ? $exportData["name"] : $exportData["docid"],
                    '',
                    'ADD',
                    self::OTHERTAGPREFIX . $this->family->name
                );
            }
            fclose($fout);

            $this->putcsv($filename, $atags);

            $this->sortData($filename);
            $this->zip->addFile($filename, basename($filename));
            $this->contentDescription['Others'] = basename($filename);
            $this->infoInstall[] = basename($filename);
        }
    }

    public function getDocumentToExport()
    {
        if (!$this->family) {
            throw new Exception("Need set a family use ::setFamily()");
        }

        if ($this->family->profid != $this->family->id && $this->family->profid) {
            $this->addDocumentToExport($this->family->profid, "f-profid");
        }
        if ($this->family->cprofid) {
            $this->addDocumentToExport($this->family->cprofid, "f-cprofid");
        }
        if ($this->family->dfldid) {
            $this->addDocumentToExport($this->family->dfldid, "f-dfldid");
        }
        if ($this->family->cfldid) {
            $this->addDocumentToExport($this->family->cfldid, "f-cfldid");
        }
        if ($this->family->ccvid > 0) {
            $cv = new_Doc("", $this->family->ccvid);
            $tmskid = $cv->getMultipleRawValues(\Dcp\AttributeIdentifiers\Cvdoc::cv_mskid);

            foreach ($tmskid as $imsk) {
                $this->addDocumentToExport($imsk, "f-cvmask");
            }
            $this->addDocumentToExport($this->family->ccvid, "cv");
        }
        if ($this->family->wid > 0) {
            /**
             * @var \WDoc $wdoc
             */
            $wdoc = \new_Doc("", $this->family->wid);
            $this->addWorkflowToExport($wdoc);
        }

        return $this->exportDocuments;
    }

    protected function addWorkflowToExport(\WDoc $wdoc)
    {
        $tattr = $wdoc->getAttributes();
        foreach ($tattr as $ka => $oa) {
            if ($oa->type == "docid") {
                $tdid = $wdoc->getMultipleRawValues($ka);
                foreach ($tdid as $did) {
                    if ($did != "") {
                        $m = getTDoc("", $did);
                        if ($m) {
                            if ($m["doctype"] !== "C") {
                                $tmoredoc[$m["initid"]] = "wrel";

                                if (!empty($m["cv_mskid"])) {
                                    $tmskid = $this->family->rawValueToArray($m["cv_mskid"]);
                                    foreach ($tmskid as $kmsk => $imsk) {
                                        if ($imsk != "") {
                                            $msk = getTDoc("", $imsk);
                                            if ($msk) {
                                                $tmoredoc[$msk["id"]] = "wmask";
                                                $this->addDocumentToExport($msk["initid"], "w-cvmask");
                                            }
                                        }
                                    }
                                }
                                if (!empty($m["tm_tmail"])) {
                                    $tmskid = $this->family->rawValueToArray(str_replace('<BR>', "\n", $m["tm_tmail"]));
                                    foreach ($tmskid as $kmsk => $imsk) {
                                        if ($imsk != "") {
                                            $msk = getTDoc("", $imsk);
                                            if ($msk) {
                                                $tmoredoc[$msk["id"]] = "tmask";
                                                $this->addDocumentToExport($msk["initid"], "w-tmmail");
                                            }
                                        }
                                    }
                                }
                                $this->addDocumentToExport($m["initid"], "w-conf");
                            }
                        }
                    }
                }
            }
        }
        $this->addDocumentToExport($this->family->wid, "wid");
    }

    public function getOtherDocumentToExport()
    {
        if (!$this->family) {
            throw new Exception("Need set a family use ::setFamily()");
        }

        $s = new \SearchDoc($this->family->dbaccess);
        $s->addFilter("atags ~ '\\\\y%s%s\\\\y'", self::OTHERTAGPREFIX, $this->family->name);
        $s->setObjectReturn(true);
        $s->setOrder("fromid, name");
        $dl = $s->search()->getDocumentList();
        foreach ($dl as $other) {
            $this->addDocumentToExport($other->initid, "other", $other->name);
        }

        return array_filter($this->exportDocuments, function ($a) {
            return ($a["index"] === "other");
        });
    }

    protected function addDocumentToExport($docid, $index, $name = '')
    {
        if ($docid) {
            $this->exportDocuments[] = array(
                "index" => $index,
                "docid" => $docid,
                "name" => $name
            );
        }
    }

    protected function sortData($filename)
    {
        $handle = fopen($filename, "r");
        $data = [];
        while (($data[] = fgetcsv($handle, 0, $this->csvSeparator, $this->csvEnclosure)) !== false) {
            ;
        }
        fclose($handle);
        array_pop($data);

        $profilAccess = $others = $masks = [];
        $profilLink = [];

        foreach ($data as $k => $cells) {
            if (!empty($cells)) {
                if ($cells[0] === "PROFIL") {
                    if (empty($cells[2]) || $cells[2][0] !== ":") {
                        $profilLink[] = $cells;
                    } else {
                        $profilAccess[] = $cells;
                    }
                    unset($data[$k]);
                }
            }
        }
        if ($profilAccess) {
            usort($profilAccess, function ($a, $b) {
                return strcasecmp($a[1], $b[1]);
            });
            array_unshift($profilAccess, array(
                "//PROFIL",
                "Id",
                "AccountType",
                "Reset",
                "Access"
            ));
        }
        if ($profilLink) {
            usort($profilLink, function ($a, $b) {
                return strcasecmp($a[1], $b[1]);
            });
            array_unshift($profilLink, array(
                "//PROFIL",
                "Id",
                "Reference"
            ));
        }

        $masks = $this->extractData($data, "MASK");
        $cv = $this->extractData($data, "CVDOC");
        $mt = $this->extractData($data, "MAILTEMPLATE");
        $tm = $this->extractData($data, "TIMER");
        $pdoc = $this->extractData($data, "PDOC");
        $pdir = $this->extractData($data, "PDIR");

        $outData = array_merge($masks, $mt, $tm, $cv, $pdoc, $pdir, $data, $profilAccess, $profilLink);
        /* print "<pre>-------------\n";

        foreach ($outData as $k=>$row) {

            print "$k)".substr(htmlspecialchars(implode(";", $row)), 0, 40)."\n";
        }
        print "</pre>";*/

        unlink($filename);
        $this->putcsv($filename, $outData);
    }

    protected function extractData(&$data, $key)
    {
        $out = [];
        $order = [];
        foreach ($data as $k => $cells) {
            if (!empty($cells)) {
                if (isset($cells[1]) && $cells[1] === $key) {
                    if ($cells[0] === "ORDER") {
                        if (!$order) {
                            $order["fam$k"] = $data[$k - 1];
                            $order["order$k"] = $data[$k];
                        }
                        unset($data[$k - 1]);
                        unset($data[$k]);
                    } else {
                        $out[] = $cells;
                        unset($data[$k]);
                    }
                }
            }
        }
        usort($out, function ($a, $b) {
            return strcasecmp($a[2], $b[2]);
        });
        return array_values(array_merge($order, $out));
    }

    protected function putcsv($filename, $data)
    {
        $handler = fopen($filename, "a");
        if (!$handler) {
            throw new Exception(sprintf("Cannot open \"%s\" to write csv", $filename));
        }

        foreach ($data as $row) {
            fputcsv($handler, $row, $this->csvSeparator, $this->csvEnclosure);
        }
        fclose($handler);
    }
}
