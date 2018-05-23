<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * document to present a report on one family document
 */

namespace Anakeen\SmartStructures\Report;

use Anakeen\Core\Internal\Format\DateAttributeValue;
use Anakeen\Core\SEManager;
use \SmartStructure\Attributes\Report as MyAttributes;

class ReportHooks extends \SmartStructure\Dsearch
{
    public $defaultedit = "FREEDOM:EDITREPORT";
    public $defaultview = "FREEDOM:VIEWREPORT";

    public $cviews
        = array(
            "FREEDOM:VIEWREPORT",
            "FREEDOM:VIEWMINIREPORT:T"
        );
    public $eviews
        = array(
            "FREEDOM:EDITREPORT"
        );

    protected $attributeGrants = array();

    /**
     * public because use in RSS
     *
     */
    public function _getInternals()
    {
        return array(
            "title" => _("doctitle"),
            "revdate" => _("revdate"),
            "id" => _("document id"),
            "revision" => _("revision"),
            "state" => _("step"),
            "owner" => _("owner")
        );
    }


    /**
     * Generate data struct to csv export of a report
     *
     * @param boolean $isPivotExport if is pivot true
     * @param string  $pivotId
     * @param string  $separator
     * @param string  $dateFormat
     * @param boolean $refresh       true to refresh the doc before export
     * @param bool    $stripHtmlTags
     * @param string  $renderNumber
     * @return array
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Dcp\Db\Exception
     * @throws \Dcp\Fmtc\Exception
     * @throws \Dcp\SearchDoc\Exception
     */
    public function generateCSVReportStruct(
        $isPivotExport = false,
        $pivotId = "id",
        $separator = ".",
        $dateFormat = "US",
        $refresh = true,
        $stripHtmlTags = false,
        $renderNumber = "format"
    ) {
        require_once 'WHAT/Class.twoDimensionalArray.php';
        require_once 'FDL/Class.SearchDoc.php';

        $famId = $this->getRawValue("se_famid", 1);
        $limit = $this->getRawValue("rep_limit", "ALL");
        $order = $this->getRawValue("rep_idsort", "title");

        $this->setStatus(_("Doing search request"));
        $search = new \SearchDoc($this->dbaccess, $famId);
        $search->dirid = $this->initid;
        $search->slice = $limit;
        $search->orderby = trim($order . " " . $this->getRawValue("rep_ordersort"));
        $search->setObjectReturn();
        // print_r($search->getSearchInfo());
        $famDoc = SEManager::createDocument($famId);
        $tcols = $this->getMultipleRawValues("rep_idcols");
        $tcolsOption = $this->getMultipleRawValues("rep_displayoption");
        $searchCols = $tcols;
        $searchCols[] = "cvid";
        $searchCols[] = "wid";
        $search->returnsOnly($searchCols);

        if ($isPivotExport) {
            $search->search();
            $this->setStatus(_("Doing render"));
            return $this->generatePivotCSV($search, $tcols, $famDoc, $pivotId, $refresh, $separator, $dateFormat, $stripHtmlTags, $renderNumber);
        } else {
            $this->setStatus(_("Doing render"));
            return $this->generateBasicCSV($search, $tcols, $tcolsOption, $famDoc, $refresh, $separator, $dateFormat, $stripHtmlTags, $renderNumber);
        }
    }

    public static function setStatus($s)
    {
        global $action;
        $expVarName = $action->getParam("exportSession");
        if ($expVarName) {
            $action->Register($expVarName, array("status" => $s));
        }
    }

    protected function generatePivotCSV(
        \SearchDoc $search,
        array $columns,
        \Anakeen\Core\Internal\SmartElement $famDoc,
        $pivotId,
        $refresh,
        $separator,
        $dateFormat,
        $stripHtmlTags,
        $renderNumber = "format"
    ) {
        $convertFormat = array(
            "dateFormat" => $dateFormat,
            'decimalSeparator' => $separator,
            'stripHtmlTags' => $stripHtmlTags,
            'renderNumber' => $renderNumber
        );

        $pivotColumnName = uniqid();

        $singleAttributes = array();
        $multipleAttributes = array();
        $resultSingleArray = array();
        $resultMultipleArray = array();

        $internals = $this->_getInternals();
        //Generate column organisation
        $resultSingleArray[$pivotColumnName] = array();

        foreach ($columns as $currentColumnID) {
            $attributeObject = $famDoc->getAttribute($currentColumnID);
            if (!$attributeObject) {
                $singleAttributes[] = $currentColumnID;
                $resultSingleArray[$currentColumnID] = array();
            } elseif ($attributeObject->isMultiple()) {
                if ($attributeObject->getOption('multiple') == "yes" && !$attributeObject->inArray()) {
                    $multipleAttributes[$currentColumnID] = array();
                    $multipleAttributes[$currentColumnID][] = $currentColumnID;
                    $resultMultipleArray[$currentColumnID] = array();
                    $resultMultipleArray[$currentColumnID][$pivotColumnName] = array();
                    $resultMultipleArray[$currentColumnID][$currentColumnID] = array();
                } else {
                    $arrayID = $attributeObject->fieldSet->id;
                    if (!isset($multipleAttributes[$arrayID])) {
                        $multipleAttributes[$arrayID] = array();
                        $resultMultipleArray[$arrayID] = array();
                        $resultMultipleArray[$arrayID][$pivotColumnName] = array();
                    }
                    $multipleAttributes[$arrayID][] = $currentColumnID;
                    $resultMultipleArray[$arrayID][$currentColumnID] = array();
                }
            } else {
                $singleAttributes[] = $currentColumnID;
                $resultSingleArray[$currentColumnID] = array();
            }
        }
        //Get Value
        $nbDoc = $search->count();
        $k = 0;
        while ($currentDoc = $search->getNextDoc()) {
            $k++;
            if ($k % 10 == 0) {
                $this->setStatus(sprintf(_("Pivot rendering %d/%d"), $k, $nbDoc));
            }
            if ($refresh) {
                $currentDoc->refresh();
            }
            $pivotAttribute = $famDoc->getAttribute($pivotId);
            $pivotValue = $pivotAttribute ? $this->getCellValue($currentDoc, $pivotAttribute, $convertFormat) : $this->convertInternalElement($pivotId, $currentDoc);
            $resultSingleArray[$pivotColumnName][] = $pivotValue;
            foreach ($singleAttributes as $currentColumnID) {
                $currentAttribute = $famDoc->getAttribute($currentColumnID);
                $resultSingleArray[$currentColumnID][] = $currentAttribute ? $this->getCellValue($currentDoc, $currentAttribute, $convertFormat)
                    : $this->convertInternalElement($currentColumnID, $currentDoc);
            }
            $nbElement = 0;
            foreach ($multipleAttributes as $currentKey => $currentArrayID) {
                foreach ($currentArrayID as $currentColumnID) {
                    $currentAttribute = $famDoc->getAttribute($currentColumnID);
                    $nbElement = count($currentDoc->getMultipleRawValues($currentColumnID));
                    for ($i = 0; $i < $nbElement; $i++) {
                        $resultMultipleArray[$currentKey][$currentColumnID][] = $this->getCellValue($currentDoc, $currentAttribute, $convertFormat, $i);
                    }
                }
                for ($i = 0; $i < $nbElement; $i++) {
                    $resultMultipleArray[$currentKey][$pivotColumnName][] = $pivotValue;
                }
            }
        }
        //Generate result array
        $firstRow = array();
        $twoDimStruct = new \TwoDimensionStruct();
        //Generate first line
        $firstRow[] = _("REPORT_pivot");
        $twoDimStruct->addColumn($resultSingleArray[$pivotColumnName]);

        foreach ($singleAttributes as $currentColumnID) {
            $currentAttribute = $famDoc->getAttribute($currentColumnID);
            $firstRow[] = $currentAttribute ? $currentAttribute->getLabel() : $internals[$currentColumnID];
            $twoDimStruct->addColumn($resultSingleArray[$currentColumnID]);
        }
        //Generate content
        foreach ($multipleAttributes as $currentKey => $currentArrayID) {
            $firstRow[] = "";
            $emptyArray = array(
                ""
            );
            $twoDimStruct->addColumn($emptyArray);
            $firstRow[] = _("REPORT_pivot");
            $twoDimStruct->addColumn($resultMultipleArray[$currentKey][$pivotColumnName]);
            foreach ($currentArrayID as $currentColumnID) {
                $currentAttribute = $famDoc->getAttribute($currentColumnID);
                $firstRow[] = $currentAttribute ? $currentAttribute->getLabel() : $internals[$currentColumnID];
                $twoDimStruct->addColumn($resultMultipleArray[$currentKey][$currentColumnID]);
            }
        }

        if ($twoDimStruct->insertRow(0, $firstRow, true) == null) {
            var_export($twoDimStruct->getLastErrorMessage());
        }

        return $twoDimStruct->getArray();
    }

    protected function getCellValue(\Anakeen\Core\Internal\SmartElement $doc, \Anakeen\Core\SmartStructure\BasicAttribute $oa, $format, $index = -1)
    {
        return $oa->getTextualValue($doc, $index, $format);
    }

    /**
     * Generate a basic CSV export
     *
     * @param \SearchDoc                          $search  the result of the report
     * @param array                               $columns an array of id
     * @param array                               $displayOptions
     * @param \Anakeen\Core\Internal\SmartElement $famDoc  the associated family doc
     *
     * @param                                     $refresh
     * @param                                     $separator
     * @param                                     $dateFormat
     * @param bool                                $stripHtmlFormat
     * @param string                              $renderNumber
     * @return array
     * @throws \Dcp\Fmtc\Exception
     */
    protected function generateBasicCSV(
        \SearchDoc $search,
        array $columns,
        array $displayOptions,
        \Anakeen\Core\Internal\SmartElement $famDoc,
        $refresh,
        $separator,
        $dateFormat,
        $stripHtmlFormat = true,
        $renderNumber = "format"
    ) {
        $fc = new \Anakeen\Core\Internal\FormatCollection();
        $dl = $search->getDocumentList();
        $fc->useCollection($dl);

        $htmlNoAccess = new \DOMDocument();
        $htmlNoAccess->loadHTML($this->getFamilyParameterValue(MyAttributes::rep_noaccesstext));

        $fc->setNoAccessText(trim($htmlNoAccess->textContent));
        if ($separator) {
            $fc->setDecimalSeparator($separator);
        }
        $fc->relationIconSize = 0;
        $fc->stripHtmlTags($stripHtmlFormat);
        switch ($dateFormat) {
            case 'US':
                $fc->setDateStyle(DateAttributeValue::isoWTStyle);
                break;

            case 'FR':
                $fc->setDateStyle(DateAttributeValue::frenchStyle);
                break;

            case 'ISO':
                $fc->setDateStyle(DateAttributeValue::isoStyle);
                break;
        }
        $isAttrInArray = array();
        foreach ($columns as $k => $col) {
            if (empty($col)) {
                unset($columns[$k]);
            } else {
                if ($famDoc->getAttribute($col)) {
                    $fc->addAttribute($col);
                    $isAttrInArray[$col] = $famDoc->getAttribute($col)->inArray();
                } else {
                    $fc->addProperty($col);
                }
            }
        }

        $fc->setLongtextMultipleBrToCr(" "); // longtext are in a single line if multiple
        $fc->setNc('-');
        $fc->setHookAdvancedStatus(function ($s) {
            \SmartStructure\Report::setStatus($s);
        });
        $r = $fc->render();
        $this->setStatus(_("Doing csv render"));
        $out = array();
        $line = array();
        foreach ($columns as $kc => $col) {
            if (isset(\Anakeen\Core\Internal\SmartElement::$infofields[$col]["label"])) {
                $line[$kc] = _(\Anakeen\Core\Internal\SmartElement::$infofields[$col]["label"]);
            } else {
                $line[$kc] = $famDoc->getLabel($col);
                if ($displayOptions[$kc] == "docid") {
                    $line[$kc] .= ' (' . _("report:docid") . ')';
                }
            }
        }
        $out[] = $line;
        foreach ($r as $k => $render) {
            $line = array();
            foreach ($columns as $kc => $col) {
                $cellValue = '';
                if (isset($render["attributes"][$col])) {
                    $oa = $famDoc->getAttribute($col);
                    $cellValue = \Anakeen\Core\Internal\FormatCollection::getDisplayValue($render["attributes"][$col], $oa, -1, array(
                        'displayDocId' => ($displayOptions[$kc] == "docid"),
                        'stripHtmlTags' => $stripHtmlFormat
                    ));
                    if ($renderNumber === "raw"
                        && in_array($oa->type, array(
                            "int",
                            "double",
                            "money"
                        ))) {
                        if (is_array($render["attributes"][$col])) {
                            $numValues = [];
                            foreach ($render["attributes"][$col] as $arender) {
                                $oneValue = $arender->value;
                                if ($separator) {
                                    $oneValue = str_replace(".", $separator, $oneValue);
                                }
                                $numValues[] = $oneValue;
                            }
                            $cellValue = implode("\n", $numValues);
                        } else {
                            $cellValue = $render["attributes"][$col]->value;
                            if ($separator) {
                                $cellValue = str_replace(".", $separator, $cellValue);
                            }
                        }
                    }
                } else {
                    if (isset($render["properties"][$col])) {
                        $cellValue = $render["properties"][$col];
                        if (is_object($cellValue)) {
                            $cellValue = $cellValue->displayValue;
                        }
                    }
                }
                $line[] = $cellValue;
            }
            $out[] = $line;
        }

        return $out;
    }

    protected function convertInternalElement($internalName, \Anakeen\Core\Internal\SmartElement $doc)
    {
        switch ($internalName) {
            case "revdate":
                return strftime("%x %T", $doc->getRawValue($internalName));
            case "state":
                return $doc->getStatelabel();
            case "title":
                return $doc->getHTMLTitle();
            case "id":
                return $doc->id;
            case "owner":
                return $doc->owner;
            default:
                return $doc->getRawValue($internalName);
        }
    }
}
