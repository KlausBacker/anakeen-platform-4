<?php
require_once ("FDL/Class.Doc.php");
function exportFamilyConfig(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Export Family Configuration Form");
    $familyId = $usage->addRequiredParameter("family", "Family identifier");
    $csvEnclosure = $usage->addOptionalParameter("csvenclosure", "CSV enclosure", array(
        "'",
        '"'
    ) , '"');
    $csvSeparator = $usage->addOptionalParameter("csvseparator", "CSV separator", array(
        ";",
        ","
    ) , ';');
    $usage->setStrictMode(false);
    $usage->verify();
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
    
    Http_DownloadFile($filename, sprintf("%s.zip", $family->name) , "application/x-zip", false, false, true);
}
