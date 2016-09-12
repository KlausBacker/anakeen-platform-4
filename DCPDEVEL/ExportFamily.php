<?php
namespace Dcp\Devel;

use Dcp\Exception;

class ExportFamily
{
    
    protected $csvEnclosure = '"';
    protected $csvSeparator = ',';
    /**
     * @var \DocFam
     */
    protected $family = null;
    protected $data = [];
    protected $exportDocuments = [];
    protected $workDirectory;
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
        
        $filename = tempnam(getTmpDir() , "exportFam") . ".zip";
        $this->workDirectory = sprintf("%s/%s", getTmpDir() , uniqid("exportFam.d"));
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
        $this->exportInfoXml();
        $this->zip->close();
        return $filename;
    }
    
    protected function exportInfoXml()
    {
        $filename = sprintf("%s/%s__info.xml", $this->workDirectory, $this->family->name);
        $dom = new \DOMDocument();
        $dom->formatOutput = true;
        $root = $dom->createElement("module");
        $install = $dom->createElement("pre-install");
        $upgrade = $dom->createElement("pre-upgrade");
        
        $struct = $dom->createElement("module");
        $struct = $dom->createElement("module");
        //<process command="./wsh.php --api=importDocuments --file=./@APPNAME@/zoo_espece__INIT_DATA.csv --csv-separator=auto --csv-enclosure='&quot;'"/>
        if ($this->csvEnclosure === '"') {
            $enclosureArg = "'\"'";
        } elseif ($this->csvEnclosure === "'") {
            $enclosureArg = "\"'\"";
        } else {
            $enclosureArg = $this->csvEnclosure;
        }
        
        foreach ($this->infoInstall as $installFile) {
            $command = sprintf('./wsh.php --api=importDocuments --file=./@APPNAME@/%s --csv-separator="%s" --csv-enclosure=%s', $installFile, $this->csvSeparator, $enclosureArg);
            $process = $dom->createElement("process");
            $process->setAttribute("command", $command);
            $install->appendChild($process);
        }
        foreach ($this->infoUpgrade as $installFile) {
            $command = sprintf('./wsh.php --api=importDocuments --file=./@APPNAME@/%s --csv-separator="%s" --csv-enclosure=%s', $installFile, $this->csvSeparator, $enclosureArg);
            $process = $dom->createElement("process");
            $process->setAttribute("command", $command);
            $upgrade->appendChild($process);
        }
        
        $root->appendChild($install);
        $root->appendChild($upgrade);
        
        $dom->appendChild($root);
        
        file_put_contents($filename, $dom->saveXML());
        
        $this->zip->addFile($filename, basename($filename));
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
        
        $this->moveProfilAtTheEnd($filename);
        $this->zip->addFile($filename, basename($filename));
        $this->infoInstall[] = basename($filename);
    }
    /** FAMNAME__STRUCT.csv¶
     BEGIN
     PROFID
     CVID
     PARAM
     INITIAL
     DEFAULT
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
        $this->infoInstall[] = basename($filename);
        $this->infoUpgrade[] = basename($filename);
    }
    
    protected function getStruct($param = false)
    {
        //	idattr	idframe	label	T	A	type	ord	vis	need	link	phpfile	phpfunc	elink	constraint	option
        $sql = sprintf("select * from docattr where docid=%d", $this->family->id);
        if ($param) {
            $sql.= "and usefor = 'Q'";
            $key = "PARAM";
        } else {
            $sql.= "and usefor != 'Q'";
            $key = "ATTR";
        }
        $sql.= " order by ordered";
        
        simpleQuery("", $sql, $attrs);
        
        $data[] = ["//$key", "Id", "Parent", "Label", "isTitle", "isAbstract", "Type", "Order", "Visibility", "Needed", "Link", "PhpFile", "PhpFunc", "Elink", "Constraint", "Options"];
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
            
            $data[] = [$key, $attr["id"], $attr["frameid"], $attr["labeltext"], $attr["title"], $attr["abstract"], $attr["type"], $attr["ordered"], $attr["visibility"], $attr["needed"], $attr["link"], $attr["phpfile"], $attr["phpfunc"], $attr["elink"], $attr["phpconstraint"], $attr["options"]];
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
            // @TODO when is in vault
            $data[] = ["ICON", $this->family->icon];
            copy(sprintf("%s/Images/%s", DEFAULT_PUBDIR, $this->family->icon) , sprintf("%s/%s", $this->workDirectory, $this->family->icon));
            $this->zip->addFile(sprintf("%s/Images/%s", DEFAULT_PUBDIR, $this->family->icon) , $this->family->icon);
        }
        
        if ($this->family->schar) {
            $data[] = ["SCHAR", $this->family->schar];
        }
        
        if ($this->family->usefor) {
            $data[] = ["USEFOR", $this->family->usefor];
        }
        if ($this->family->methods) {
            $data[] = ["METHODS", $this->family->methods];
        }
        if ($this->family->classname) {
            $data[] = ["CLASS", $this->family->classname];
        }
        if ($this->family->atags) {
            $data[] = ["TAGS", $this->family->atags];
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
        
        $this->moveProfilAtTheEnd($filename);
        $this->putcsv($filename, $data);
        
        $this->zip->addFile($filename, basename($filename));
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
                        throw new Exception(sprintf("Export Document %s (%s)not alive", $exportData["docid"], $exportData["index"]));
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
            
            $this->moveProfilAtTheEnd($filename);
            $this->zip->addFile($filename, basename($filename));
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
            $wdoc = \new_Doc("", $this->family->wid);
            
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
        
        return $this->exportDocuments;
    }
    
    protected function addDocumentToExport($docid, $index)
    {
        if ($docid) {
            $this->exportDocuments[] = array(
                "index" => $index,
                "docid" => $docid
            );
        }
    }
    
    protected function moveProfilAtTheEnd($filename)
    {
        $content = file($filename);
        $fout = fopen($filename, "w");
        $profil = [];
        foreach ($content as $line) {
            if (substr($line, 0, 6) === "PROFIL") {
                $profil[] = $line;
            } else {
                fputs($fout, $line);
            }
        }
        
        if ($profil) {
            fputcsv($fout, array(
                "//PROFIL",
                "Id",
                "AccountType",
                "Reset",
                "Access"
            ) , $this->csvSeparator, $this->csvEnclosure);
            foreach ($profil as $line) {
                
                fputs($fout, $line);
            }
        }
        
        fclose($fout);
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
