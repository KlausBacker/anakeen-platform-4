<?php
/**
 * Import documents
 *
 */

namespace Anakeen\Exchange;

use Anakeen\Core\DbManager;
use Anakeen\Core\SmartStructure\BasicAttribute;
use \Anakeen\Core\SmartStructure\DocAttr;
use Anakeen\Core\Utils\Postgres;
use Anakeen\Exception;

class ImportDocumentDescription
{
    const attributePrefix = ":ATTR:";
    const documentPrefix = ":DOC:";
    private $dirid = 0;
    private $analyze = false;
    private $policy = "update";
    private $reinit = false;
    private $csvSeparator = Utils::SEPCHAR;
    private $csvEnclosure = '';
    private $csvLinebreak = '\n';
    private $beginLine = 0;
    private $familyIcon = 0;
    private $nLine = 0;
    private $nbDoc = 0;
    private $ods2CsvFile = '';
    private $reset = array();
    /* Store erroneous family's ORDER line to prevent import of document from that family */
    private $badOrderErrors = array();
    /**
     * @var bool verify attribute access
     */
    private $verifyAttributeAccess = true;
    /**
     * @var \StructAttribute
     */
    private $structAttr = null;
    /**
     * @var array
     */
    private $colOrders = [];
    private $colKeys = [];
    /**
     * @var array
     */
    private $tcr = array();
    private $dbaccess = '';
    private $needCleanStructure = false;
    private $needCleanParamsAndDefaults = false;
    private $importFileName = '';
    /*
     * @var ressource
    */
    private $fdoc;
    /**
     * @var \Anakeen\Core\SmartStructure
     */
    private $doc;
    /**
     * Store attributes defined/updated by the current import session.
     *
     * @var array
     */
    private $importedAttribute = array();
    /**
     * Store known logical names
     *
     * @var array
     */
    private $knownLogicalNames = array();
    private $userIds = [];

    /**
     * @param string $importFile
     *
     * @throws \Anakeen\Exception
     */
    public function __construct($importFile = "")
    {
        if ($importFile) {
            if (Utils::seemsODS($importFile)) {
                $this->ods2CsvFile = Utils::ods2csv($importFile);
                $this->fdoc = fopen($this->ods2CsvFile, "r");
            } else {
                $this->fdoc = fopen($importFile, "r");
            }
            if (!$this->fdoc) {
                throw new \Anakeen\Exception(sprintf("no import file found : %s", $importFile));
            }
            $this->importFileName = $importFile;
        }
        $this->dbaccess = \Anakeen\Core\DbManager::getDbAccess();
    }

    /**
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }

    public function analyzeOnly($analyze)
    {
        $this->analyze = $analyze;
    }

    public function setPolicy($policy)
    {
        if (!$policy) {
            $policy = "update";
        }
        $this->policy = $policy;
    }

    public function setImportDirectory($dirid)
    {
        $this->dirid = $dirid;
    }

    public function reinitAttribute($reinit)
    {
        $this->reinit = $reinit;
    }

    public function reset($reset)
    {
        if ($reset && !is_array($reset)) {
            $reset = array(
                $reset
            );
        }
        $this->reset = $reset;
    }

    public function setComma($comma)
    {
        $this->csvSeparator = $comma;
    }

    public function setCsvOptions($csvSeparator = ';', $csvEnclosure = '"', $csvLinebreak = '\n')
    {
        $this->csvSeparator = $csvSeparator;
        $this->csvEnclosure = $csvEnclosure;
        $this->csvLinebreak = $csvLinebreak;

        $this->setAutoCsvOptions();
        return array(
            "separator" => $this->csvSeparator,
            "enclosure" => $this->csvEnclosure,
            "linebreak" => $this->csvLinebreak
        );
    }

    /**
     * Detect csv options - separator and enclosure arguments are modified if set to auto
     *
     * @param         $csvFileName
     * @param string &$separator need to set to 'auto' to detect
     * @param string &$enclosure need to set to 'auto' to detect
     *
     * @return array associaive array "enclosure", "separator" keys
     * @throws \Anakeen\Exception
     */
    public static function detectAutoCsvOptions($csvFileName, &$separator = 'auto', &$enclosure = 'auto')
    {
        $content = file_get_contents($csvFileName);
        if ($separator == 'auto') {
            $detector = new \Anakeen\Core\Utils\Csv\Detector();
            $detected = $detector->detect($content);
            if (!isset($detected['separator']['char']) || $detected['separator']['char'] === null) {
                throw new \Anakeen\Exception(sprintf("cannot find csv separator in %s file", $csvFileName));
            }
            $separator = $detected['separator']['char'];
        }
        if ($enclosure == 'auto') {
            $detector = new \Anakeen\Core\Utils\Csv\Detector();
            $detector->separators = array(
                $separator
            );
            $detected = $detector->detect($content);
            if (isset($detected['enclosure']['char']) && $detected['enclosure']['char'] !== null) {
                $enclosure = $detected['enclosure']['char'];
            } else {
                $enclosure = '';
            }
        }
        return array(
            "separator" => $separator,
            "enclosure" => $enclosure
        );
    }

    protected function setAutoCsvOptions()
    {
        if (!$this->ods2CsvFile) {
            if (($this->csvSeparator == 'auto') || ($this->csvEnclosure == 'auto')) {
                $this->detectAutoCsvOptions($this->importFileName, $this->csvSeparator, $this->csvEnclosure);
            }
        } else {
            // converted from ods
            // separator is ;  enclosure double quote
            $this->csvEnclosure = '"';
            $this->csvSeparator = ';';
            $this->csvLinebreak = '\n';
        }
    }


    public function importCsvFile()
    {
        \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(300);

        $this->nbDoc = 0; // number of imported document
        $this->structAttr = null;
        $this->colOrders = array();
        $this->ods2CsvFile = "";

        $this->nLine = 0;
        $this->beginLine = 0;
        $csvLinebreak = $this->csvLinebreak;
        if (!$this->csvSeparator && !$csvLinebreak) {
            $csvLinebreak = '\n';
        }
        $dataLines = [];

        while (!feof($this->fdoc)) {
            if (!$this->csvEnclosure) {
                $buffer = rtrim(fgets($this->fdoc));
                $data = explode($this->csvSeparator, $buffer);
                $data = array_map(function ($v) use ($csvLinebreak) {
                    return str_replace(array(
                        $csvLinebreak,
                        Utils::ALTSEPCHAR
                    ), array(
                        "\n",
                        ';'
                    ), $v);
                }, $data);
            } else {
                // Use enclosure as escape : solution to not use escape character
                $data = fgetcsv($this->fdoc, 0, $this->csvSeparator, $this->csvEnclosure, $this->csvEnclosure);

                if ($data === false) {
                    continue;
                }
                if ($csvLinebreak) {
                    $data = array_map(function ($v) use ($csvLinebreak) {
                        return str_replace($csvLinebreak, "\n", $v);
                    }, $data);
                }
            }
            $this->nLine++;

            if (!\Anakeen\Core\Utils\Strings::isUTF8($data)) {
                $data = array_map("utf8_encode", $data);
            }
            $dataLines[] = $data;
        }

        fclose($this->fdoc);

        $this->importData($dataLines);
        if ($this->ods2CsvFile) {
            unlink($this->ods2CsvFile);
        } // temporary csvfile
        return $this->tcr;
    }

    public function importData($datas)
    {
        \Anakeen\Core\Utils\System::setMaxExecutionTimeTo(300);

        $this->nbDoc = 0; // number of imported document
        $this->structAttr = null;
        $this->colOrders = array();
        $this->ods2CsvFile = "";

        $this->nLine = 0;
        $this->beginLine = 0;
        foreach ($datas as $data) {
            $this->nLine++;


            // return structure
            if (count($data) < 1) {
                continue;
            }
            $this->tcr[$this->nLine] = array(
                "err" => "",
                "msg" => "",
                "specmsg" => "",
                "folderid" => 0,
                "foldername" => "",
                "filename" => "",
                "title" => "",
                "id" => "",
                "values" => array(),
                "familyid" => 0,
                "familyname" => "",
                "code" => trim($data[0]),
                "action" => " "
            );
            $this->tcr[$this->nLine]["title"] = substr($data[0], 0, 10);
            $data[0] = trim($data[0]);
            switch ($data[0]) {
                // -----------------------------------

                case "BEGIN":
                    $this->doBegin($data);
                    break;
                // -----------------------------------

                case "END":
                    $this->doEnd($data);

                    break;

                case "RESET":
                    $this->doReset($data);
                    break;
                // -----------------------------------

                case "DOC":
                    $this->doDoc($data);
                    break;
                // -----------------------------------

                case "SEARCH":
                    $this->doSearch($data);

                    break;
                // -----------------------------------

                case "TYPE":
                    if (!$this->doc) {
                        break;
                    }

                    $this->doc->doctype = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf("set doctype to '%s'", $data[1]);
                    break;
                // -----------------------------------

                case "GENVERSION":
                    if (!$this->doc) {
                        break;
                    }

                    $this->doc->genversion = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf("generate version '%s'", $data[1]);
                    break;
                // -----------------------------------

                case "MAXREV":
                    if (!$this->doc) {
                        break;
                    }

                    $this->doc->maxrev = intval($data[1]);
                    $this->tcr[$this->nLine]["msg"] = sprintf("max revision '%d'", $data[1]);
                    break;
                // -----------------------------------

                case "ICON": // for family
                    $this->doIcon($data);
                    break;

                case "DOCICON":
                    $this->doDocIcon($data);
                    break;

                case "DOCATAG":
                    $this->doDocAtag($data);
                    break;

                case "DFLDID":
                    $this->doDfldid($data);
                    break;

                case "CFLDID":
                    $this->doCfldid($data);
                    break;

                case "WID":
                    $this->doWid($data);
                    break;

                case "CVID":
                    $this->doCvid($data);
                    break;

                case "SCHAR":
                    if (!$this->doc) {
                        break;
                    }

                    $this->doc->schar = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf("set special characteristics to \"%s\"", $data[1]);
                    break;
                // -----------------------------------

                case "CLASS":
                    $this->doClass($data);
                    break;

                case "METHOD":
                    $this->doMethod($data);
                    break;

                case "USEFORPROF":
                    if (!$this->doc) {
                        break;
                    }

                    $this->doc->usefor = "P";
                    $this->tcr[$this->nLine]["msg"] = sprintf(
                        "\"change useforprop property \" %s\"",
                        $this->doc->usefor
                    );
                    break;

                case "USEFOR":
                    if (!$this->doc) {
                        break;
                    }

                    $this->doc->usefor = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf("change usefor property \"%s\"", $this->doc->usefor);
                    break;

                case "TAG":
                    $this->doATag($data);
                    break;

                case "CPROFID":
                    $this->doCprofid($data);
                    break;

                case "CFALLID":
                    $this->doCFallid($data);
                    break;

                case "PROFID":
                    $this->doProfid($data);
                    break;

                case "DEFAULT":
                    $this->doDefault($data);
                    break;

                case "INITIAL":
                    $this->doInitial($data);
                    break;

                case "UPDTATTR":
                    $this->doUpdtattr($data);
                    break;

                case "PARAM":
                case "ATTR":
                case "MODATTR":
                    $this->doAttr($data);
                    break;

                case "ORDER":
                    $this->doOrder($data);
                    break;

                case "KEYS":
                    $this->doKeys($data);
                    break;

                case "TAGABLE":
                    $this->doTagable($data);
                    break;

                case "PROFIL":
                    $this->doProfil($data);
                    break;

                case "ACCESS":
                    $this->doAccess($data);
                    break;

                case "PROP":
                    $this->doProp($data);
                    break;
                case "ENUM":
                    $this->doEnum($data);
                    break;

                default:
                    // uninterpreted line
                    unset($this->tcr[$this->nLine]);
            }
        }

        return $this->tcr;
    }

    /**
     * add application tag
     *
     * @param array $data
     */
    protected function doATag(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $value = $data[2] ?? true;
        if ($value === "") {
            $value = true;
        }
        $err = "";
        if (!$this->analyze) {
            $err = $this->doc->addATag($data[1], $value);
        }
        if (!$err) {
            $this->tcr[$this->nLine]["msg"] = sprintf("change application tag to '%s'", $this->doc->atags);
        } else {
            $this->tcr[$this->nLine]["err"] = "ATAG:" . $err;
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze BEGIN
     *
     * @param array $data line of description file
     */
    protected function doBegin(array $data)
    {
        $err = "";
        $data = array_map("trim", $data);
        // search from name or from id
        try {
            $this->doc = null;
            $this->beginLine = $this->nLine;
            $check = new \CheckBegin();
            $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
            if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
                $this->tcr[$this->nLine]["action"] = "warning"; #"warning"
                return;
            }
            if ($this->tcr[$this->nLine]["err"] == "") {
                if (($data[3] == "") || ($data[3] == "-")) {
                    $this->doc = new \Anakeen\Core\SmartStructure(
                        $this->dbaccess,
                        \Anakeen\Core\SEManager::getFamilyIdFromName($data[5]),
                        '',
                        0,
                        false
                    );
                } else {
                    $this->doc = new \Anakeen\Core\SmartStructure($this->dbaccess, $data[3], '', 0, false);
                }

                $this->familyIcon = "";

                if (!$this->doc->isAffected()) {
                    if (!$this->analyze) {
                        $this->doc = new \Anakeen\Core\SmartStructure($this->dbaccess);

                        if (isset($data[3]) && ($data[3] > 0)) {
                            $this->doc->id = $data[3];
                        } // static id
                        if (is_numeric($data[1])) {
                            $this->doc->fromid = $data[1];
                        } else {
                            $this->doc->fromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
                        }
                        if (isset($data[5])) {
                            $this->doc->name = $data[5];
                        } // internal name
                        $err = $this->doc->add();
                    }
                    $this->tcr[$this->nLine]["msg"] = sprintf("create %s family %s", $data[2], $data[5]);
                    $this->tcr[$this->nLine]["action"] = "added";
                } else {
                    $this->tcr[$this->nLine]["action"] = "updated";
                    $this->tcr[$this->nLine]["msg"] = sprintf(
                        "updating \"%s\" smart structure : \"%s\"",
                        $data[5],
                        $data[2] ?: $this->doc->title
                    );
                }
                if ($data[5]) {
                    \Anakeen\Core\SmartStructure\SmartStructureImport::deleteGenFiles($data[5]);
                }
                if ($data[1] && ($data[1] != '-')) {
                    if ($data[1] == '--') {
                        $this->doc->fromid = 0;
                    } elseif (is_numeric($data[1])) {
                        $this->doc->fromid = $data[1];
                    } else {
                        $this->doc->fromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
                    }
                }
                if ($data[2] && ($data[2] != '-')) {
                    $this->doc->title = $data[2];
                }
                if ($data[4] && ($data[4] != '-')) {
                    $this->doc->classname = $data[4];
                } // new classname for familly
                if ($data[4] == "--") {
                    $this->doc->classname = '';
                }
                $this->tcr[$this->nLine]["err"] .= $check->checkClass($data, $this->doc)->getErrors();

                if ($data[5] && ($data[5] != '-')) {
                    $this->doc->name = $data[5];
                } // internal name
                $this->tcr[$this->nLine]["err"] .= $err;

                if ($this->reinit) {
                    $this->tcr[$this->nLine]["msg"] .= sprintf("reinit all attributes");
                    if ($this->analyze) {
                        return;
                    }
                    $oattr = new DocAttr($this->dbaccess);
                    $oattr->docid = intval($this->doc->id);
                    if ($oattr->docid > 0) {
                        $err = $oattr->query(sprintf("delete from docattr where docid=%d", $oattr->docid));
                        // $err .= $oattr->exec_query(sprintf("update docfam set defval=null,param=null  where id=%d",  $oattr->docid));
                    }
                    $this->tcr[$this->nLine]["err"] .= $err;
                }
                if ($this->reset) {
                    foreach ($this->reset as $reset) {
                        $this->doReset(array(
                            "RESET",
                            $reset
                        ));
                    }
                }
            } else {
                $this->tcr[$this->nLine]["err"] .= $err;
            }
        } catch (Exception $e) {
            $this->tcr[$this->nLine]["err"] .= $e->getMessage();
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze END
     *
     * @param array $data line of description file
     */
    protected function doEnd(array $data)
    {
        if (!$this->doc) {
            return;
        }

        // add messages
        $msg = sprintf("record new smart structure configuration \"%s\" : \"%s\"", $this->doc->name, $this->doc->title);
        $this->tcr[$this->nLine]["msg"] = $msg;

        $ferr = '';
        for ($i = $this->beginLine; $i < $this->nLine; $i++) {
            if (!empty($this->tcr[$i]["err"])) {
                $ferr .= $this->tcr[$i]["err"];
            }
        }
        if ($this->analyze) {
            $this->nbDoc++;
            if ($ferr) {
                $this->tcr[$this->beginLine]["action"] = "warning";
            }
            return;
        }
        if ((count($data) > 3) && ($data[3] != "")) {
            $this->doc->doctype = "S";
        }
        if ($ferr == "") {
            $this->doc->mdate = \Anakeen\Core\Utils\Date::getNow(true);
            $this->doc->modify();


            $check = new \CheckEnd($this);
            if ($this->doc->doctype == "C") {
                global $tFamIdName;
                $check->checkMaxAttributes($this->doc);
                $err = $check->getErrors();

                if ($err && $this->analyze) {
                    $this->tcr[$this->nLine]["msg"] .= sprintf("Element can't be perfectly analyzed, some error might occurs or be corrected when importing");
                    $this->tcr[$this->nLine]["action"] = "warning";
                    return;
                }
                if ($err == '') {
                    if (strpos($this->doc->usefor, "W") !== false) {
                        $checkW = new \CheckWorkflow($this->doc->classname, $this->doc->name);
                        $checkCr = $checkW->verifyWorkflowClass();
                        if (count($checkCr) > 0) {
                            if (count($checkCr) > 0) {
                                $err = implode(",", $checkCr);
                                $this->tcr[$this->nLine]["err"] .= $err;
                            }
                        }
                        if (!$err) {
                            $this->doc->getHooks()->trigger(\Anakeen\SmartHooks::POSTIMPORT);
                        }
                    }

                    if (!$err) {
                        try {
                            //special to add calculated attributes
                            $msg = \Anakeen\Core\SmartStructure\SmartStructureImport::refreshPhpPgDoc(
                                $this->dbaccess,
                                $this->doc->id
                            );
                            if ($msg !== '') {
                                $this->tcr[$this->nLine]["err"] .= $msg;
                                $this->tcr[$this->nLine]["action"] = "ignored";
                                $this->tcr[$this->beginLine]["action"] = "ignored";
                                return;
                            }
                            if (isset($tFamIdName)) {
                                $tFamIdName[$this->doc->name] = $this->doc->id;
                            } // refresh getFamIdFromName for multiple family import
                            $checkCr = \CheckDb::verifyDbFamily($this->doc->id);
                            if (count($checkCr) > 0) {
                                $this->tcr[$this->nLine]["err"] .= \ErrorCode::getError(
                                    'ATTR1700',
                                    implode(",", $checkCr)
                                );
                            } else {
                                // Need to update child family in case of new attribute
                                $childsFams = ($this->doc->getChildFam());
                                foreach ($childsFams as $famInfo) {
                                    \Anakeen\Core\SmartStructure\SmartStructureImport::createDocFile(
                                        $this->dbaccess,
                                        $famInfo
                                    );
                                }
                            }
                        } catch (\Anakeen\Exception $e) {
                            $this->tcr[$this->nLine]["err"] .= $e->getMessage();
                        }
                    }
                } else {
                    $this->tcr[$this->nLine]["err"] .= $err;
                }
            }

            if ($this->needCleanParamsAndDefaults) {
                $this->needCleanParamsAndDefaults = false;
                $this->cleanDefaultAndParametersValues();
            }

            $this->tcr[$this->nLine]["err"] .= $check->check($data, $this->doc)->getErrors();
            if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
                $this->tcr[$this->nLine]["msg"] .= sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
                $this->tcr[$this->nLine]["action"] = "warning";
                return;
            }

            if ((!$this->analyze) && ($this->familyIcon != "")) {
                $this->doc->changeIcon($this->familyIcon);
            }
            if (!$this->tcr[$this->nLine]["err"]) {
                $this->tcr[$this->nLine]["msg"] .= $this->doc->getHooks()->trigger(\Anakeen\SmartHooks::POSTIMPORT);
                $check->checkMaxAttributes($this->doc);
                $this->tcr[$this->nLine]["err"] = $check->getErrors();
                if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
                    $this->tcr[$this->nLine]["msg"] .= sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
                    $this->tcr[$this->nLine]["action"] = "warning";
                    return;
                }
            }

            $this->doc->addHistoryEntry("Update by importation");

            $this->nbDoc++;

            \Anakeen\Core\SEManager::cache()->removeDocumentById($this->doc->id);
            if ($this->tcr[$this->nLine]["err"]) {
                $this->tcr[$this->beginLine]["action"] = "ignored";
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
        } else {
            $this->tcr[$this->beginLine]["action"] = "ignored";
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
        if ($this->needCleanStructure) {
            $this->needCleanStructure = false;
            if (!$this->tcr[$this->nLine]["err"]) {
                $this->cleanStructure();
            }
        }
    }

    /**
     * Delete undeclared sql columns
     */
    protected function cleanStructure()
    {
        if (!$this->doc) {
            return;
        }

        $orphanAttributes = \CheckDb::getOrphanAttributes($this->doc->id);
        if ($orphanAttributes) {
            $sql = array();
            foreach ($orphanAttributes as $orphanAttrId) {
                $sql[] = sprintf("alter table doc%d drop column if exists %s cascade; ", $this->doc->id, $orphanAttrId);

                $this->tcr[$this->nLine]["msg"] .= "\nDestroy values for \"$orphanAttrId\".";
            }
            $sql[] = sprintf(
                "create view family.\"%s\" as select * from doc%d",
                strtolower($this->doc->name),
                $this->doc->id
            );

            foreach ($sql as $aSql) {
                \Anakeen\Core\DbManager::query($aSql);
            }
        }
    }

    protected function cleanDefaultAndParametersValues()
    {
        $defs = $this->doc->getOwnDefValues();
        foreach ($defs as $aid => $v) {
            if (!$this->doc->getAttribute($aid)) {
                $this->doc->setDefValue($aid, '', false);
                $this->tcr[$this->nLine]["msg"] .= "\nClear default value \"$aid\".";
            }
        }
        $defs = $this->doc->getOwnParams();
        foreach ($defs as $aid => $v) {
            if (!$this->doc->getAttribute($aid)) {
                $this->doc->setParam($aid, '', false);
                $this->tcr[$this->nLine]["msg"] .= "\nClear parameter value \"$aid\".";
            }
        }

        $this->doc->modify();
    }

    /**
     * analyze RESET²
     *
     * @param array $data line of description file
     */
    protected function doReset(array $data)
    {
        $err = "";
        $data = array_map("trim", $data);
        $check = new \CheckReset();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }

        if (!$this->tcr[$this->nLine]["err"]) {
            switch (strtolower($data[1])) {
                case 'attributes':
                    $this->tcr[$this->nLine]["msg"] .= sprintf("reinit all fields");
                    if ($this->analyze) {
                        return;
                    }

                    $sql = sprintf("delete from docattr where docid=%d", $this->doc->id);
                    \Anakeen\Core\DbManager::query($sql);

                    $this->needCleanParamsAndDefaults = true;
                    break;

                case 'default':
                    $this->tcr[$this->nLine]["msg"] .= sprintf("Reset defaults values");
                    if ($this->doc) {
                        $this->doc->defaultvalues = '{}';
                    }
                    break;

                case 'parameters':
                    $this->tcr[$this->nLine]["msg"] .= sprintf("Reset parameters values");
                    if ($this->doc) {
                        $this->doc->param = '';
                    }
                    break;

                case 'enums':
                    $this->tcr[$this->nLine]["msg"] .= sprintf("Reset enums definition \"%s\"", $data[2]);
                    $enumName = $data[2];
                    $sql = sprintf("delete from docenum where name='%s'", pg_escape_string($enumName));
                    \Anakeen\Core\DbManager::query($sql);
                    break;

                case 'properties':
                    $this->tcr[$this->nLine]["msg"] .= sprintf("reinit all properties");
                    if ($this->analyze) {
                        return;
                    }
                    if ($this->doc) {
                        $this->doc->resetPropertiesParameters();
                    }
                    break;

                case 'structure':
                    $this->tcr[$this->nLine]["msg"] .= sprintf("Reset attribute structure");
                    if ($this->analyze) {
                        return;
                    }
                    $sql = sprintf("delete from docattr where docid=%d", $this->doc->id);
                    \Anakeen\Core\DbManager::query($sql);
                    $this->needCleanStructure = true;
                    $this->needCleanParamsAndDefaults = true;
                    break;
            }
        } else {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
        $this->tcr[$this->nLine]["err"] .= $err;
    }

    /**
     * analyze DOC
     *
     * @param array $data line of description file
     */
    protected function doDoc(array $data)
    {
        $check = new \CheckDoc();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        $famName = $check->getParsedFamName();
        if ($this->tcr[$this->nLine]["err"]) {
            if ($this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
                $this->tcr[$this->nLine]["action"] = "warning";
            } else {
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
            return;
        }
        if ($famName !== false && isset($this->badOrderErrors[$famName])) {
            /* Do not import the document if the ORDER line of its family was erroneous */
            if ($this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf(
                    "Cannot import document because the ORDER line for family '%s' is incorrect: %s",
                    $famName,
                    $this->badOrderErrors[$famName]
                );
                $this->tcr[$this->nLine]["action"] = "warning";
            } else {
                $this->tcr[$this->nLine]["msg"] = sprintf(
                    "Cannot import document because the ORDER line for family '%s' is incorrect: %s",
                    $famName,
                    $this->badOrderErrors[$famName]
                );
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
            return;
        }
        // case of specific order
        if (is_numeric($data[1])) {
            $fromid = $data[1];
        } else {
            $fromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
        }

        if (isset($this->colKeys[$fromid])) {
            $tk = $this->colKeys[$fromid];
        } else {
            $tk = array(
                "title"
            );
        }

        $torder = array();
        if (isset($this->colOrders[$fromid])) {
            $torder = $this->colOrders[$fromid];
        }
        // $this->tcr[$this->nLine] = csvAddDoc($this->dbaccess, $data, $this->dirid, $this->analyze, '', $this->policy, $tk, array() , $torder);
        $oImportDocument = new ImportSingleDocument();
        if ($tk) {
            $oImportDocument->setKey($tk);
        }
        if ($torder) {
            $oImportDocument->setOrder($torder);
        }
        $oImportDocument->analyzeOnly($this->analyze);
        $oImportDocument->setPolicy($this->policy);
        $oImportDocument->setTargetDirectory($this->dirid);
        /**
         * Append current document's logical name to list of known logical names
         * and configure the importer to use this list to check for unknown
         * logical names
         */
        if ($data[2] != '' && !in_array($data[2], $this->knownLogicalNames)) {
            $this->knownLogicalNames[] = $data[2];
        }
        $oImportDocument->setKnownLogicalNames($this->knownLogicalNames);

        $this->tcr[$this->nLine] = $oImportDocument->import($data)->getImportResult();

        if ($this->tcr[$this->nLine]["err"] == "") {
            $this->nbDoc++;
        } else {
            $check->addError($this->tcr[$this->nLine]["err"]);
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
        }
    }

    /**
     * analyze SEARCH
     *
     * @param array $data line of description file
     */
    protected function doSearch(array $data)
    {
        $err = '';
        if ($data[1] != '') {
            $this->tcr[$this->nLine]["id"] = $data[1];
            /**
             * @var \Anakeen\SmartStructures\Search\SearchHooks $search
             */
            $search = \Anakeen\Core\SEManager::getDocument($data[1]);
            if (!$search || !$search->isAffected()) {
                $search = \Anakeen\Core\SEManager::createDocument(5);
                if (!$this->analyze) {
                    if ($data[1] && is_numeric($data[1])) {
                        $search->id = $data[1]; // static id
                    }
                    $err = $search->add();
                    if ($data[1] && !is_numeric($data[1])) {
                        $search->setLogicalName($data[1]);
                    }
                }
                $this->tcr[$this->nLine]["msg"] = sprintf("update %s search", $data[3]);
                $this->tcr[$this->nLine]["action"] = "updated";
            }
        } else {
            $search = \Anakeen\Core\SEManager::createDocument(5);
            if (!$this->analyze) {
                $err = $search->add();
            }
            $this->tcr[$this->nLine]["msg"] = sprintf("add %s search", $data[3]);
            $this->tcr[$this->nLine]["action"] = "added";
            $this->tcr[$this->nLine]["err"] .= $err;
        }
        if (($err != "") && ($search->id > 0)) { // case only modify
            $search->select($search->id);
        }
        if (!$this->analyze) {
            // update title in finish
            $search->title = $data[3];
            $err = $search->modify();
            $this->tcr[$this->nLine]["err"] .= $err;

            if (($data[4] != "")) { // specific search
                $err = $search->AddStaticQuery($data[4]);
                $this->tcr[$this->nLine]["err"] .= $err;
            }

            if ($data[2] != '') { // dirid

                /**
                 * @var \Anakeen\SmartStructures\Dir\DirHooks $dir
                 */
                $dir = \Anakeen\Core\SEManager::getDocument($data[2]);
                if ($dir && $dir->isAlive() && method_exists($dir, "insertDocument")) {
                    $dir->insertDocument($search->id);
                }
            }
        }
        $this->nbDoc++;
    }

    /**
     * analyze DOCICON
     *
     * @param array $data line of description file
     */
    protected function doDocIcon(array $data)
    {
        $idoc = \Anakeen\Core\SEManager::getDocument($data[1]);
        if (!$this->analyze) {
            $idoc->changeIcon($data[2]);
        }
        if ($idoc->isAlive()) {
            $this->tcr[$this->nLine]["msg"] = sprintf("document %s : set icon to '%s'", $idoc->title, $data[2]);
        } else {
            $this->tcr[$this->nLine]["err"] = sprintf("no change icon : document %s not found", $data[1]);
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze DOCATAG
     *
     * @param array $data line of description file
     */
    protected function doDocAtag(array $data)
    {
        $check = new \CheckDocATag();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $idoc = \Anakeen\Core\SEManager::getDocument($data[1]);

        $i = 4;
        $tags = [];
        while (!empty($data[$i])) {
            $tags[] = $data[$i];
            $i++;
        }

        $tagAction = $data[3];
        if (!$tagAction) {
            $tagAction = "ADD";
        }

        if (!$this->analyze) {
            if ($tagAction === "SET") {
                $idoc->atags = '';
                if (!$tags) {
                    $err = $idoc->modify(true, array("atags"), true);
                    if ($err) {
                        $this->tcr[$this->nLine]["err"] = $err;
                    }
                }
            }
            foreach ($tags as $tag) {
                if ($tagAction === "DELETE") {
                    $err = $idoc->delATag($tag);
                } else {
                    $err = $idoc->addATag($tag);
                }
                if ($err) {
                    $this->tcr[$this->nLine]["err"] = $err;
                }
            }
        }
        switch ($tagAction) {
            case "ADD":
                $this->tcr[$this->nLine]["msg"] = sprintf("Add atags \"%s\"", implode("\", \"", $tags));
                break;

            case "DELETE":
                $this->tcr[$this->nLine]["msg"] = sprintf("Del atags \"%s\"", implode("\", \"", $tags));
                break;

            case "SET":
                $this->tcr[$this->nLine]["msg"] = sprintf("Set atags \"%s\"", implode("\", \"", $tags));
                break;
        }
    }

    /**
     * analyze ICON
     *
     * @param array $data line of description file
     */
    protected function doIcon(array $data)
    {
        if (empty($data[1])) {
            $this->tcr[$this->nLine]["msg"] = sprintf("No Icon specified");
        } elseif (($this->doc && $this->doc->icon == "") || (isset($data[2]) && $data[2] === "force=yes")) {
            $this->familyIcon = $data[1]; // reported to end section
            $this->tcr[$this->nLine]["msg"] = sprintf("set icon to '%s'", $data[1]);
        } else {
            $this->tcr[$this->nLine]["msg"] = sprintf("icon already set. Update is skipped");
        }
    }

    /**
     * analyze DFLDID
     *
     * @param array $data line of description file
     */
    protected function doDfldid(array $data)
    {
        if (!$this->doc) {
            return;
        }
        if (!isset($data[1])) {
            $data[1] = '';
        }
        $check = new \CheckDfldid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if (!$this->tcr[$this->nLine]["err"]) {
            $fldid = 0;
            if ($data[1] == "auto") {
                if ($this->doc->dfldid == "") {
                    if (!$this->analyze) {
                        // create auto
                        include_once("Legacy/LegacyDocManager.php");
                        $fldid = createAutoFolder($this->doc);
                        $this->tcr[$this->nLine]["msg"] .= sprintf("create default folder (id [%d])\n", $fldid);
                    }
                } else {
                    $fldid = $this->doc->dfldid;
                    $this->tcr[$this->nLine]["msg"] = sprintf("default folder already set. Auto ignored");
                }
            } elseif (is_numeric($data[1])) {
                $fldid = $data[1];
            } else {
                $fldid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
            }
            $this->doc->dfldid = $fldid;
            $this->tcr[$this->nLine]["msg"] .= sprintf("set default folder to '%s'", $data[1]);
        } else {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze CFLDID
     *
     * @param array $data line of description file
     */
    protected function doCfldid(array $data)
    {
        if (!$this->doc) {
            return;
        }

        $check = new \CheckCfldid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if (!$this->tcr[$this->nLine]["err"]) {
            if (is_numeric($data[1])) {
                $cfldid = $data[1];
            } else {
                $cfldid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
            }
            $this->doc->cfldid = $cfldid;
            $this->tcr[$this->nLine]["msg"] = sprintf("set primary folder to '%s'", $data[1]);
        } else {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze WID
     *
     * @param array $data line of description file
     */
    protected function doWid(array $data)
    {
        if (!$this->doc) {
            return;
        }
        if (!isset($data[1])) {
            $data[1] = '';
        }
        $check = new \CheckWid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (is_numeric($data[1])) {
            $wid = $data[1];
        } else {
            $wid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
        }
        if ($data[1]) {
            try {
                $wdoc = \Anakeen\Core\SEManager::getDocument($wid);
                if (!$wdoc || !$wdoc->isAlive()) {
                    $this->tcr[$this->nLine]["err"] = sprintf("WID : workflow '%s' not found", $data[1]);
                } else {
                    if (!is_subclass_of($wdoc, \Anakeen\SmartStructures\Wdoc\WDocHooks::class)) {
                        $this->tcr[$this->nLine]["err"] = sprintf("WID : workflow '%s' is not a workflow", $data[1]);
                    } else {
                        $this->doc->wid = $wdoc->id;
                    }
                }
                $this->tcr[$this->nLine]["msg"] = sprintf("set default workflow to '%s'", $data[1]);
            } catch (Exception $e) {
                $this->tcr[$this->nLine]["err"] = sprintf("WID : %s", $e->getMessage());
            }
            if ($this->tcr[$this->nLine]["err"]) {
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
        } else {
            $this->doc->wid = '';

            $this->tcr[$this->nLine]["msg"] = "unset default workflow";
        }
    }

    /**
     * analyze CVID
     *
     * @param array $data line of description file
     */
    protected function doCvid(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $check = new \CheckCvid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (is_numeric($data[1])) {
            $cvid = $data[1];
        } else {
            $cvid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
        }

        if ($data[1]) {
            try {
                $cvdoc = \Anakeen\Core\SEManager::getDocument($cvid);
                if (!$cvdoc->isAlive()) {
                    $this->tcr[$this->nLine]["err"] = sprintf("CVID : view control '%s' not found", $data[1]);
                } else {
                    $this->doc->ccvid = $cvdoc->id;
                }
                $this->tcr[$this->nLine]["msg"] = sprintf("set default view control to \"%s\"", $data[1]);
            } catch (Exception $e) {
                $this->tcr[$this->nLine]["err"] = sprintf("CVID : %s", $e->getMessage());
            }
            if ($this->tcr[$this->nLine]["err"]) {
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
        } else {
            $this->doc->ccvid = '';

            $this->tcr[$this->nLine]["msg"] = "unset default view control";
        }
    }

    /**
     * analyze CLASS
     *
     * @param array $data line of description file
     */
    protected function doClass(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $data = array_map("trim", $data);

        $check = new \CheckClass();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $this->tcr[$this->nLine]["msg"] = sprintf("class \"%s\"", $data[1]);
        $this->doc->classname = $data[1];
    }

    /**
     * analyze METHOD
     *
     * @param array $data line of description file
     */
    protected function doMethod(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $data = array_map("trim", $data);
        $check = new \CheckMethod();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (!isset($data[1])) {
            $aMethod = null;
        } else {
            $aMethod = $data[1];
        }
        $s1 = ($aMethod) ? $aMethod[0] : '';
        if (($s1 == "+") || ($s1 == "*")) {
            if ($s1 == "*") {
                $method = $aMethod;
            } else {
                $method = substr($aMethod, 1);
            }

            if ($this->doc->methods == "") {
                $this->doc->methods = $method;
            } else {
                $this->doc->methods .= "\n$method";
                // not twice
                $tmeth = explode("\n", $this->doc->methods);
                $tmeth = array_unique($tmeth);
                $this->doc->methods = implode("\n", $tmeth);
            }
        } else {
            $this->doc->methods = $aMethod;
        }

        $this->tcr[$this->nLine]["msg"] = sprintf("change methods to '%s'", $this->doc->methods);
        if ($this->doc->methods) {
            $tmethods = explode("\n", $this->doc->methods);
            foreach ($tmethods as $method) {
                $fileMethod = ($method && $method[0] == '*') ? substr($method, 1) : $method;
                if (!file_exists(sprintf(DEFAULT_PUBDIR . "/Apps/FDL/%s", $fileMethod))) {
                    $this->tcr[$this->nLine]["err"] .= sprintf("Method file '%s' not found.", $fileMethod);
                }
            }
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze CFALLID
     *
     * @param array $data line of description file
     */
    protected function doCFallid(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $check = new \CheckCfallid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (is_numeric($data[1])) {
            $pid = $data[1];
        } else {
            $pid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
        }
        $this->doc->cfallid = $pid;
        $this->tcr[$this->nLine]["msg"] = sprintf(
            "\tchange default creation field access reference to \"%s\"",
            $data[1]
        );
    }

    /**
     * analyze CPROFID
     *
     * @param array $data line of description file
     */
    protected function doCprofid(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $check = new \CheckCprofid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (is_numeric($data[1])) {
            $pid = $data[1];
        } else {
            $pid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
        }
        $this->doc->cprofid = $pid;
        $this->tcr[$this->nLine]["msg"] = sprintf("\tchange default creation profile reference to \"%s\"", $data[1]);
    }

    /**
     * analyze PROFID
     *
     * @param array $data line of description file
     */
    protected function doProfid(array $data)
    {
        if (!$this->doc) {
            return;
        }

        $check = new \CheckProfid();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (is_numeric($data[1])) {
            $pid = $data[1];
        } else {
            $pid = \Anakeen\Core\SEManager::getIdFromName($data[1]);
        }
        $this->doc->accessControl()->setProfil($pid); // change profile
        $this->tcr[$this->nLine]["msg"] = sprintf("\tchange profile reference to '%s'", $data[1]);
    }

    /**
     * analyze INITIAL
     *
     * @param array $data line of description file
     */
    protected function doInitial(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $check = new \CheckInitial($this);
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (!array_key_exists(2, $data)) {
            $data[2] = null;
        }
        $attrid = trim(strtolower($data[1]));
        $newValue = $data[2];
        $opt = (isset($data[3])) ? trim(strtolower($data[3])) : null;
        $force = (str_replace(" ", "", $opt) == "force=yes");
        $params = $this->doc->getOwnParams();
        $previousValue = isset($params[$attrid]) ? $params[$attrid] : null;
        if ((!empty($previousValue)) && (!$force)) {
            // reset default
            $this->tcr[$this->nLine]["msg"] = sprintf(
                "keep default value %s : %s. No use %s",
                $attrid,
                $previousValue,
                $data[2]
            );
        } else {
            if ($force || ($previousValue === null)) {
                $err = $this->doc->setParam($attrid, $newValue, false);
                $this->tcr[$this->nLine]["err"] = $err;
                $this->tcr[$this->nLine]["msg"] = "reset default parameter";
            }
            $this->tcr[$this->nLine]["msg"] .= sprintf("add default value %s %s", $attrid, $data[2]);
        }
    }

    /**
     * analyze DEFAULT
     *
     * @param array $data line of description file
     */
    protected function doDefault(array $data)
    {
        if (!$this->doc) {
            return;
        }
        $check = new \CheckDefault();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (!array_key_exists(2, $data)) {
            $data[2] = '';
        }

        $attrid = trim(strtolower($data[1]));
        $defv = $data[2];
        $opt = (isset($data[3])) ? trim(strtolower($data[3])) : null;
        $force = (str_replace(" ", "", $opt) == "force=yes");
        $ownDef = $this->doc->getOwnDefValues();
        if ((!empty($ownDef[$attrid])) && (!$force)) {
            // reset default
            $this->tcr[$this->nLine]["msg"] = sprintf(
                "keep default value %s : %s. No use %s",
                $attrid,
                print_r($ownDef[$attrid], true),
                $data[2]
            );
        } else {
            $this->doc->setDefValue($attrid, $defv, false);
            if ($force || (!$this->doc->getParameterRawValue($attrid))) {
                // TODO : not really exact here : must verify if it is really a parameter
                //$this->doc->setParam($attrid, $defv);
                //$this->tcr[$this->nLine]["msg"] = "reset default parameter";
            }
            $this->tcr[$this->nLine]["msg"] .= sprintf("add default value \"%s\" to \"%s\"", $attrid, $data[2]);
        }
    }

    /**
     * analyze ACCESS
     *
     * @param array $data line of description file
     */
    protected function doAccess(array $data)
    {
        $check = new \CheckAccess();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $action)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (ctype_digit(trim($data[1]))) {
            $wid = trim($data[1]);
        } else {
            $pid = \Anakeen\Core\SEManager::getIdFromName(trim($data[1]));
            $tdoc = \Anakeen\Core\SEManager::getRawData($pid, ["us_whatid"]);
            $wid = $tdoc["us_whatid"];
        }
        $ns = $data[2];

        $this->tcr[$this->nLine]["msg"] = "user #$wid";
        array_shift($data);
        array_shift($data);
        array_shift($data);

        $q = new \Anakeen\Core\Internal\QueryDb("", \Acl::class);
        $la = $q->Query(0, 0, "TABLE");
        $tacl = array();
        foreach ($la as $k => $v) {
            $tacl[$v["name"]] = $v["id"];
        }

        $p = new \Permission();
        $p->id_user = $wid;
        foreach ($data as $v) {
            $v = trim($v);
            if ($v != "") {
                if ($this->analyze) {
                    $this->tcr[$this->nLine]["msg"] .= "\n" . sprintf("try add acl %s", $v);
                    $this->tcr[$this->nLine]["action"] = "added";
                    continue;
                }
                if (substr($v, 0, 1) == '-') {
                    $aclneg = true;
                    $v = substr($v, 1);
                } else {
                    $aclneg = false;
                }
                // Add namespace
                $v = $ns . '::' . $v;

                if (isset($tacl[$v])) {
                    $p->id_acl = $tacl[$v];
                    if ($aclneg) {
                        $p->id_acl = -$p->id_acl;
                    }
                    $p->deletePermission($p->id_user, $p->id_acl);
                    $err = $p->add();
                    if ($err) {
                        $this->tcr[$this->nLine]["err"] .= "\n$err";
                    } else {
                        if ($aclneg) {
                            $this->tcr[$this->nLine]["msg"] .= "\n" . sprintf("add negative acl %s", $v);
                        } else {
                            $this->tcr[$this->nLine]["msg"] .= "\n" . sprintf("add acl %s", $v);
                        }
                    }
                } else {
                    $this->tcr[$this->nLine]["err"] .= "\n" . sprintf("unknow acl %s", $v);
                }
            }
        }


        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * analyze TAGABLE
     *
     * @param array $data tagable parameter
     */
    protected function doTagable(array $data)
    {
        if (!$this->doc) {
            return;
        }
        if (class_exists("CheckTagable")) {
            /** @noinspection PhpUndefinedClassInspection
             * Defined in dynacase-tags module
             */
            $check = new CheckTagable();
        } else {
            $this->tcr[$this->nLine]["err"] = \ErrorCode::getError('PROP0102', "TAGABLE", "dynacase-tags");
            $this->tcr[$this->nLine]["action"] = "ignored";
            error_log("ERROR:" . $this->tcr[$this->nLine]["err"]);
            return;
        }
        /**
         * @var \CheckData $check
         */
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $this->doc->tagable = $data[1] === "no" ? "" : $data[1];
        $this->tcr[$this->nLine]["msg"] = sprintf("change tagable parameter to '%s'", $this->doc->tagable);
    }

    /**
     * analyze PROFIL
     *
     * @param array $data line of description file
     */
    protected function doProfil(array $data)
    {
        $check = new \CheckProfil();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (ctype_digit(trim($data[1]))) {
            $pid = trim($data[1]);
        } else {
            $pid = \Anakeen\Core\SEManager::getIdFromName(trim($data[1]));
        }

        if (!($pid > 0)) {
            $this->tcr[$this->nLine]["err"] = sprintf("profil id unknow \"%s\"", $data[1]);
        } else {
            \Anakeen\Core\SEManager::cache()->clear();
            /**
             * @var \Anakeen\Core\Internal\SmartElement $pdoc
             */
            $pdoc = \Anakeen\Core\SEManager::getDocument($pid);
            if ($pdoc && $pdoc->isAlive()) {
                $this->tcr[$this->nLine]["msg"] = sprintf("change profil accesses for \"%s\"", $data[1]);
                $this->tcr[$this->nLine]["action"] = "modprofil";
                if ($this->analyze) {
                    return;
                }
                $fpid = $data[2];
                if (preg_match("/:use/", $fpid)) {
                    $fpid = "";
                }
                if (($fpid != "") && (!is_numeric($fpid))) {
                    $fpid = \Anakeen\Core\SEManager::getIdFromName($fpid);
                }
                if ($fpid != "") {
                    // profil related of other profil
                    $pdoc->accessControl()->setProfil($fpid);
                    $this->tcr[$this->nLine]["err"] = $pdoc->modify(false, array(
                        "profid"
                    ), true);
                } else {
                    // specific profil
                    if ($pdoc->profid != $pid) {
                        $pdoc->accessControl()->setProfil($pid);
                        $pdoc->accessControl()->setControl(false);
                        $pdoc->disableAccessControl(); // need because new profil is not enable yet
                        $this->tcr[$this->nLine]["err"] = $pdoc->modify();
                    }

                    $defaultUseType = trim($data[2]);
                    $optprof = strtoupper(trim($data[3]));
                    $initialPerms = array();
                    $profilingHasChanged = false;
                    if ($optprof == "RESET") {
                        $pdoc->accessControl()->removeControl();
                        $this->tcr[$this->nLine]["msg"] .= "\n\t" . sprintf("reset profil \"%s\"", $pid);
                    } elseif ($optprof == "SET") {
                        $initialPerms = array_merge(
                            \DocPerm::getPermsForDoc($pdoc->id),
                            \DocPermExt::getPermsForDoc($pdoc->id)
                        );
                        $pdoc->accessControl()->removeControl();
                        $this->tcr[$this->nLine]["msg"] .= "\n\t" . sprintf("set profile \"%s\"", $pid);
                    }
                    $tacls = array_slice($data, 2);
                    foreach ($tacls as $acl) {
                        if (preg_match("/([^=]+)=(.*)/", $acl, $reg)) {
                            $tuid = explode(",", $reg[2]);
                            $aclname = trim($reg[1]);

                            $perr = "";
                            if ($optprof == "DELETE") {
                                foreach ($tuid as $uid) {
                                    $perr .= $pdoc->accessControl()->delControl($this->getProfilUid(
                                        $defaultUseType,
                                        $uid
                                    ), $aclname);
                                    $this->tcr[$this->nLine]["msg"] .= "\n\t" . sprintf(
                                        "delete access \"%s\" for \"%s\"",
                                        $aclname,
                                        $uid
                                        );
                                }
                            } else { // the "ADD" by default
                                foreach ($tuid as $uid) {
                                    $perr .= $pdoc->accessControl()->addControl($this->getProfilUid(
                                        $defaultUseType,
                                        $uid
                                    ), $aclname);
                                    $this->tcr[$this->nLine]["msg"] .= "\n\t" . sprintf(
                                        "add access \"%s\" for \"%s\"",
                                        $aclname,
                                        $uid
                                        );
                                }
                            }
                            $this->tcr[$this->nLine]["err"] = $perr;
                        }
                    }
                    if ($optprof == "SET") {
                        $newPerms = array_merge(
                            \DocPerm::getPermsForDoc($pdoc->id),
                            \DocPermExt::getPermsForDoc($pdoc->id)
                        );
                        $profilingHasChanged = (serialize($newPerms) != serialize($initialPerms));
                    }
                    if ($optprof == "RESET" || ($optprof == "SET" && $profilingHasChanged)) {
                        // need reset all documents
                        $this->tcr[$this->nLine]["msg"] .= "\n\t" . sprintf(
                            "recomputing all accesses \"%s\" for \"%s\"",
                            $pdoc->name,
                            $pdoc->getTitle()
                            );
                        $pdoc->addHistoryEntry(
                            "Recomputing profiled elements",
                            \DocHisto::INFO,
                            'RECOMPUTE_PROFILED_DOCUMENT'
                        );
                        $pdoc->accessControl()->recomputeProfiledDocument();
                    }
                }
            } else {
                $this->tcr[$this->nLine]["err"] = sprintf("profil id unknow \"%s\"", $data[1]);
            }
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    protected function getProfilUid($defaultReferenceType, $reference)
    {
        $reference = trim($reference);
        $this->extractAccount($defaultReferenceType, $reference, $type, $value);
        switch ($type) {
            case ':useAccount':
                return $this->getUserId($value);
                break;

            case ':useAttribute':
                return self::attributePrefix . $value;
                break;

            case ':useDocument':
                return self::documentPrefix . $value;
                break;

            default:
                return $value;
        }
    }

    private function extractAccount($defaultReferenceType, $reference, &$type, &$value)
    {
        if (preg_match('/^attribute\((.*)\)$/', $reference, $reg)) {
            $type = ":useAttribute";
            $value = trim($reg[1]);
        } elseif (preg_match('/^account\((.*)\)$/', $reference, $reg)) {
            $type = ":useAccount";
            $value = trim($reg[1]);
        } elseif (preg_match('/^document\((.*)\)$/', $reference, $reg)) {
            $type = ":useDocument";
            $value = trim($reg[1]);
        } else {
            $value = $reference;
            $type = $defaultReferenceType;
        }
    }

    protected function getUserId($login)
    {
        $login = mb_strtolower($login);
        if (!isset($this->userIds[$login])) {
            \Anakeen\Core\DbManager::query(
                sprintf("select id from users where login='%s'", pg_escape_string($login)),
                $uid,
                true,
                true
            );
            if (!$uid) {
                throw new \Anakeen\Exception("PRFL0204", $login);
            }
            $this->userIds[$login] = $uid;
        }
        return $this->userIds[$login];
    }

    /**
     * analyze KEYS
     *
     * @param array $data line of description file
     */
    protected function doKeys(array $data)
    {
        $check = new \CheckKeys();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (is_numeric($data[1])) {
            $orfromid = $data[1];
        } else {
            $orfromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
        }

        $this->colKeys[$orfromid] = Utils::getOrder($data);
        if (($this->colKeys[$orfromid][0] == "") || (count($this->colKeys[$orfromid]) == 0)) {
            $this->tcr[$this->nLine]["err"] = sprintf(
                "error in import keys : %s",
                implode(" - ", $this->colKeys[$orfromid])
            );
            unset($this->colKeys[$orfromid]);
            $this->tcr[$this->nLine]["action"] = "ignored";
        } else {
            $this->tcr[$this->nLine]["msg"] = sprintf(
                "new import keys : %s",
                implode(" - ", $this->colKeys[$orfromid])
            );
        }
    }

    /**
     * analyze ORDER
     *
     * @param array $data line of description file
     */
    protected function doOrder(array $data)
    {
        $check = new \CheckOrder();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        $famName = $check->getParsedFamName();
        if ($this->tcr[$this->nLine]["err"]) {
            if ($famName !== false) {
                $this->badOrderErrors[$famName] = $this->tcr[$this->nLine]["err"];
            }
            if ($this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
                $this->tcr[$this->nLine]["action"] = "warning";
            } else {
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
            return;
        }
        if ($famName !== false && isset($this->badOrderErrors[$famName])) {
            unset($this->badOrderErrors[$famName]);
        }
        if (is_numeric($data[1])) {
            $orfromid = $data[1];
        } else {
            $orfromid = \Anakeen\Core\SEManager::getFamilyIdFromName($data[1]);
        }

        $this->colOrders[$orfromid] = Utils::getOrder($data);
        $this->tcr[$this->nLine]["msg"] = sprintf(
            "prepare record order %s : %s",
            $data[1],
            implode(" - ", $this->colOrders[$orfromid])
        );
    }

    /**
     * Verify compatibility between 2 type
     *
     * @param string $curType
     * @param string $newType
     *
     * @return bool
     */
    protected function isTypeCompatible($curType, $newType)
    {
        if ($curType == $newType) {
            return true;
        }
        $tc = array(
            "docid" => "account",
            "text" => "longtext",
            "longtext" => "htmltext",
            "file" => "image",
            "image" => "file",
            "integer" => "int", // old compatibility
            "float" => "double"
            // old compatibility

        );
        return isset($tc[$curType]) && ($tc[$curType] == $newType);
    }

    protected function getFromids(int $structureId)
    {
        DbManager::query(sprintf("select getFromids(%d)", $structureId), $fromids, true, true);
        return Postgres::stringToArray($fromids);
    }

    protected function getParentAttr($attrid)
    {
        $fromids = $this->getFromids($this->doc->id);
        foreach ($fromids as $fromid) {
            $oattr = new DocAttr($this->dbaccess, array(
                $fromid,
                $attrid
            ));
            if ($oattr->isAffected()) {
                return $oattr;
            }
        }
        return null;
    }

    /**
     * analyze UPDTATTR
     *
     * @param array $data line of description file
     */
    protected function doUpdtattr(array $data)
    {
        $attrid = strtolower($data[1]);
        $oattr = new DocAttr($this->dbaccess, array(
            $this->doc->id,
            $attrid
        ));
        $modAttrActivated = false;
        if (!$oattr->isAffected()) {
            $oattr = $this->getParentAttr($attrid);

            if (!$oattr) {
                $this->tcr[$this->nLine]["err"] = \ErrorCode::getError('ATTR0104', $attrid);
            } else {
                $modAttrActivated = true;
            }
        }

        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $structAttr = new \StructAttribute();
        $structAttr->set($data);
        $iAttr = new \Anakeen\Core\Internal\ImportSmartAttr();
        if ($modAttrActivated === false) {
            $iAttr->id = $oattr->id;
            $iAttr->idfield = $oattr->frameid;
            $iAttr->label = $oattr->labeltext;
            $iAttr->isTitle = $oattr->title;
            $iAttr->isAbstract = $oattr->abstract;
            $iAttr->type = $oattr->type;
            $iAttr->order = $oattr->ordered;
            $iAttr->access = $oattr->accessibility;
            $iAttr->need = $oattr->needed;
            $iAttr->link = $oattr->link;
            $iAttr->phpfile = $oattr->phpfile;
            $iAttr->phpfunc = $oattr->phpfunc;
            $iAttr->elink = $oattr->elink;
            $iAttr->constraint = ($structAttr->constraint) ?: '';
            $iAttr->autocomplete = $structAttr->autocomplete;
            $this->doAttr($iAttr->getData("ATTR"), true);
        } else {
            $iAttr->id = $attrid;
            $iAttr->constraint = ($structAttr->constraint) ?: '';
            $iAttr->autocomplete = $structAttr->autocomplete;
            $this->doAttr($iAttr->getData("MODATTR"), true);
        }
    }

    /**
     * analyze ATTR
     *
     * @param array $data line of description file
     * @param bool $updateMode true if update mode
     * @throws \Anakeen\Core\Exception
     */
    protected function doAttr(array $data, $updateMode = false)
    {
        if (!$this->doc) {
            return;
        }
        // Temporary deprecated visibility : used RW acccess instead if cvs file used
        if ($this->importFileName && isset($data[8]) && strlen($data[8]) === 1) {
            $data[8] = 'ReadWrite';
        }

        $check = new \CheckAttr();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        if (!$this->structAttr) {
            $this->structAttr = new \StructAttribute();
        }
        $this->structAttr->set($data);

        if (trim($data[1]) == '') {
            $this->tcr[$this->nLine]["err"] .= sprintf("attr key is empty");
        } else {
            $modattr = ($data[0] == "MODATTR");
            if ($data[0] == "MODATTR") {
                $this->structAttr->id = ':' . $this->structAttr->id;
            } // to mark the modified
            $this->tcr[$this->nLine]["msg"] .= sprintf("update  smart field \"%s\"", $this->structAttr->id);
            if ($this->analyze) {
                return;
            }
            $oattr = new DocAttr($this->dbaccess, array(
                $this->doc->id,
                strtolower($this->structAttr->id)
            ));

            if ($oattr->isAffected()) {
                if ($updateMode === false && $modattr) {
                    $updateMode = true;
                }
                // modification of type is forbidden
                $curType = trim(strtok($oattr->type, '('));
                $newType = trim(strtok($this->structAttr->type, '('));
                if (!$modattr && $curType != $newType && (!$this->isTypeCompatible($curType, $newType))) {
                    $this->tcr[$this->nLine]["err"] .= sprintf(
                        "cannot change attribute %s type definition from %s to %s",
                        $this->structAttr->id,
                        $curType,
                        $newType
                    );
                }
                // modification of target is forbidden
                if (($data[0] == "PARAM") && ($oattr->usefor != 'Q')) {
                    $this->tcr[$this->nLine]["err"] .= sprintf(
                        "cannot change attribute declaration to PARAM for %s",
                        $this->structAttr->id
                    );
                } elseif (($data[0] == "ATTR") && ($oattr->usefor == 'Q')) {
                    $this->tcr[$this->nLine]["err"] .= sprintf(
                        "cannot change attribute declaration to ATTR for %s",
                        $this->structAttr->id
                    );
                }
            }

            if (!$this->tcr[$this->nLine]["err"]) {
                if ($data[0] == "PARAM") {
                    $oattr->usefor = 'Q';
                // parameters
                } elseif ($data[0] == "OPTION") {
                    $oattr->usefor = 'O';
                // options
                } else {
                    $oattr->usefor = 'N';
                    // normal
                }
                $oattr->docid = $this->doc->id;
                $oattr->id = trim(strtolower($this->structAttr->id));

                if (!$updateMode || !empty($this->structAttr->setid)) {
                    $oattr->frameid = trim(strtolower($this->structAttr->setid));
                }
                if (!$updateMode || !empty($this->structAttr->label)) {
                    $oattr->labeltext = $this->structAttr->label;
                }

                $oattr->title = ($this->structAttr->istitle == "Y") ? "Y" : "N";

                $oattr->abstract = ($this->structAttr->isabstract == "Y") ? "Y" : "N";
                if ($modattr) {
                    $oattr->abstract = $this->structAttr->isabstract;
                }

                if (!$updateMode || !empty($this->structAttr->rawType)) {
                    $oattr->type = trim($this->structAttr->rawType);
                }

                if (!$updateMode || !empty($this->structAttr->order)) {
                    $oattr->ordered = $this->structAttr->order;
                }
                if (!$updateMode || !empty($this->structAttr->access)) {
                    $oattr->accessibility = $this->structAttr->access;
                }
                $oattr->needed = ($this->structAttr->isneeded == "Y") ? "Y" : "N";
                if ($modattr) {
                    $oattr->title = $this->structAttr->istitle;
                    $oattr->needed = $this->structAttr->isneeded;
                }
                if (!$updateMode || !empty($this->structAttr->link)) {
                    $oattr->link = $this->structAttr->link;
                }
                if (!$updateMode || !empty($this->structAttr->phpfile)) {
                    $oattr->phpfile = $this->structAttr->phpfile;
                }
                if ($this->structAttr->elink) {
                    $oattr->elink = $this->structAttr->elink;
                } elseif (!$updateMode) {
                    $oattr->elink = '';
                }
                if ($this->structAttr->constraint) {
                    $oattr->phpconstraint = $this->structAttr->constraint;
                } elseif (!$updateMode) {
                    $oattr->phpconstraint = '';
                }
                if ($this->structAttr->options) {
                    $oattr->options = $this->structAttr->options;
                } elseif (!$updateMode) {
                    $oattr->options = '';
                }

                $oattr->phpfunc = $this->structAttr->phpfunc;
                if (!empty($data["props"])) {
                    $oattr->properties = json_encode($data["props"]);
                }

                /**
                 * Old Enum declaration
                 */
                if (($this->structAttr->type === "enum" && $this->structAttr->phpfile == "" && $this->structAttr->phpfunc != "")) {
                    // don't modify  enum possibilities if exists and non system
                    $oattr->phpfunc = $this->structAttr->phpfunc;


                    // don't record if enum comes from function
                    $reset = (strpos($oattr->options, "system=yes") !== false);
                    $enumName = $this->structAttr->format;

                    if ($oattr->type && !$enumName) {
                        $enumName = sprintf("%s-%s", $this->doc->name, $oattr->id);
                        $oattr->type = sprintf('%s("%s")', $this->structAttr->type, $enumName);
                    }
                    $this->recordEnum($enumName, $this->structAttr->phpfunc, $reset);
                    //$oattr->phpfunc = "-";
                }
                if ($oattr->ordered && !is_numeric($oattr->ordered)) {
                    $oattr->options .= ($oattr->options) ? "|" : "";
                    $oattr->options .= sprintf("relativeOrder=%s", $oattr->ordered);
                    $oattr->ordered = $this->nLine;
                }
                if ($modattr && $oattr->options) {
                    $parentAttr = $this->getParentAttr(trim($oattr->id, ":"));
                    $pOptions = BasicAttribute::optionsToArray($parentAttr->options);
                    $cOptions = BasicAttribute::optionsToArray($oattr->options);
                    if (!isset($cOptions["multiple"]) && !empty($pOptions["multiple"]) && $pOptions["multiple"] === "yes") {
                        $oattr->options .= "|multiple=yes";
                    }
                }
                if ($oattr->options) {
                    $cOptions = BasicAttribute::optionsToArray($oattr->options);
                    $cOptions = array_unique($cOptions);
                    $attrOptions = [];
                    foreach ($cOptions as $k => $v) {
                        $attrOptions[] = sprintf("%s=%s", $k, $v);
                    }
                    $oattr->options = implode('|', $attrOptions);
                }

                if ($oattr->isAffected()) {
                    $err = $oattr->Modify();
                } else {
                    $err = $oattr->add();
                }
                $this->addImportedAttribute($this->doc->id, $oattr);

                $this->tcr[$this->nLine]["err"] .= $err;
            }
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    /**
     * @param string $phpfunc enum flat description
     * @param bool $reset set to true to delete old items before recorded
     *
     * @return string error message
     */
    public static function recordEnum($enumName, $phpfunc, $reset = false)
    {
        static $oe = null;

        $err = '';
        if ($oe === null) {
            $oe = new \Anakeen\Core\SmartStructure\DocEnum();
        }
        $enums = array();
        \EnumAttributeTools::flatEnumNotationToEnumArray($phpfunc, $enums);
        $oe->name = $enumName;
        $oe->eorder = 0;
        if ($reset) {
            $sql = sprintf("delete from docenum where name='%s'", pg_escape_string($enumName));
            \Anakeen\Core\DbManager::query($sql);
        }

        foreach ($enums as $itemKey => $itemLabel) {
            $oe->label = $itemLabel;
            $oe->eorder++;
            $antiItemKey = str_replace("\\.", "--dot--", $itemKey);
            if (strpos($antiItemKey, '.') !== false) {
                $tkeys = explode(".", $itemKey);
                $oe->key = array_pop($tkeys);
                $oe->parentkey = array_pop($tkeys);
            } else {
                $oe->key = str_replace("\\.", ".", $itemKey);

                $oe->parentkey = '';
            }
            $err = '';
            if ($oe->exists()) {
                // $err=$oe->add();
                // " skipped [$itemKey]";
            } else {
                // " added  [$itemKey]";
                $err .= $oe->add();
            }
        }
        return $err;
    }


    /**
     * analyze PROP
     *
     * @param array $data line of description file
     */
    protected function doProp($data)
    {
        $check = new \CheckProp();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf("Element can't be perfectly analyze, some error might occur or be corrected when importing");
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }

        $propName = $check->propName;
        $values = $check->parameters;

        if ($this->analyze) {
            return;
        }

        foreach ($values as $value) {
            $pName = $value['name'];
            $pValue = $value['value'];
            if (!$this->doc->setPropertyParameter($propName, $pName, $pValue)) {
                $this->tcr[$this->nLine]["err"] .= sprintf(
                    "error storing configuration property (%s, %s, %s)",
                    $propName,
                    $pName,
                    $pValue
                );
                return;
            }
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }

    protected function doEnum($data)
    {
        $enumName = $data["name"];
        $key = $data["key"];
        $enum = new \Anakeen\Core\SmartStructure\DocEnum("", [$enumName, $key]);

        $enum->name = $data["name"];
        $enum->key = $data["key"];
        $enum->label = $data["label"];
        $enum->parentkey = $data["parentKey"];

        if ($enum->isAffected()) {
            $err = $enum->modify();
        } else {
            $err = $enum->add();
        }
        $this->tcr[$this->nLine]["err"] = $err;

        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
        $this->tcr[$this->nLine]["msg"] = sprintf("Enum \"%s\" - \"%s\" recorded", $enumName, $key);
    }

    protected function addImportedAttribute($famId, DocAttr &$oa)
    {
        if (!isset($this->importedAttribute[$famId])) {
            $this->importedAttribute[$famId] = array();
        }
        $this->importedAttribute[$famId][$oa->id] = $oa;
    }

    /**
     * @param $famId
     * @param $attrId
     * @return bool|DocAttr
     */
    public function getImportedDocAttr($famId, $attrId)
    {
        if (isset($this->importedAttribute[$famId][$attrId])) {
            return $this->importedAttribute[$famId][$attrId];
        }
        return false;
    }

    /**
     * @param $attrId
     * @return \Anakeen\Core\SmartStructure\BasicAttribute|\Anakeen\Core\SmartStructure\NormalAttribute|false|null
     */
    public function getSmartField($attrId)
    {
        if ($this->doc) {
            /** @var \Anakeen\Core\SmartStructure\DocAttr $dbattr */
            $dbattr = $this->getImportedDocAttr($this->doc->id, $attrId);

            if ($dbattr) {
                $oa = new \Anakeen\Core\SmartStructure\NormalAttribute(
                    $dbattr->id,
                    $dbattr->docid,
                    $dbattr->labeltext,
                    $dbattr->type,
                    "",
                    false,
                    0,
                    "",
                    \Anakeen\Core\SmartStructure\BasicAttribute::READWRITE_ACCESS
                );
                $oa->usefor = $dbattr->usefor;
                $oa->options = $dbattr->options;
                if ($dbattr->frameid) {
                    $pField = $this->getImportedDocAttr($this->doc->id, $dbattr->frameid);
                    $oa->fieldSet = $pField;
                }
                return $oa;
            } else {
                return $this->doc->getAttribute($attrId);
            }
        }
        return null;
    }
}
