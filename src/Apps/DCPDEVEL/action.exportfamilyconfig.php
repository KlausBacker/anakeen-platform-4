<?php
require_once("FDL/Class.Doc.php");
function exportFamilyConfig(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Export Family Configuration Form");
    $familyId = $usage->addRequiredParameter("family", "Family identifier");
    $csvEnclosure = $usage->addOptionalParameter("csvenclosure", "CSV enclosure", array(
        "'",
        '"'
    ), $action->getParam("CSV_ENCLOSURE", '"'));
    $csvSeparator = $usage->addOptionalParameter("csvseparator", "CSV separator", array(
        ";",
        ","
    ), $action->getParam("CSV_SEPARATOR", ";"));
    $usage->setStrictMode(false);
    $usage->verify();
    
    $action->setParamU("CSV_SEPARATOR", $csvSeparator);
    $action->setParamU("CSV_ENCLOSURE", $csvEnclosure);
    /**
     * @var DocFam $family
     */
    $family = new_Doc("", $familyId);
    if (!$family->isAffected()) {
        $action->exitError("Undefined family");
    }
    
    $famConf = new \Dcp\Devel\ExportFamily();
    $famConf->setFamily($family);
    $famConf->setCsvEnclosure($csvEnclosure);
    $famConf->setCsvSeparator($csvSeparator);
    
    $filename = $famConf->export();
    
    Http_DownloadFile($filename, sprintf("%s-%s.zip", $family->name, date("Ymd\\THis")), "application/x-zip", false, false, true);
}
