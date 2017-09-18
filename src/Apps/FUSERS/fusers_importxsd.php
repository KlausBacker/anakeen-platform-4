<?php
/*
 * @author Anakeen
 * @package FDL
*/

function fusers_importxsd(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Get XSD Accounts file");
    
    $complete = $usage->addOptionalParameter("complete", "Get documents XSD also", array(
        "true",
        "false"
    ) , "false") === "true";
    $usage->verify();
    
    if ($complete) {
        $schemaDirectory = sprintf("%s/%s", getTmpDir() , uniqid("accountExport"));
        mkdir($schemaDirectory);
        $export = new \Dcp\Core\ExportAccounts();
        $export->setExportSchemaDirectory($schemaDirectory);
        $export->setExportDocument(true);
        
        $search = new SearchAccount();
        $search->addFilter("users.id = 1");
        $export->setSearchAccount($search);
        $export->export();
        
        $zipFile = sprintf("%s/accountsXsd.zip", $schemaDirectory);
        $zip = new ZipArchive();
        $zip->open($zipFile, ZIPARCHIVE::CREATE);
        chdir($schemaDirectory . "/" . $export::XSDDIR);
        $handle = opendir($schemaDirectory . "/" . $export::XSDDIR);
        if ($handle) {
            while (false !== $f = readdir($handle)) {
                if (preg_match("/.(xsd)$/", $f)) {
                    $zip->addFile($f);
                }
            }
            closedir($handle);
        }
        $zip->close();
        Http_DownloadFile($zipFile, "accountsXsd.zip", "application/x-zip");
    } else {
        Http_DownloadFile("./USERCARD/Layout/accounts.xsd", "accounts.xsd", "text/xml");
    }
}
