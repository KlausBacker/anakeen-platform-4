<?php

$oldStructs = ["HUBADMINCENTERACCOUNTSVUE", "HUBADMINCENTERPARAMETERSVUE", "HUBADMINCENTERVAULTVUE", "HUBAUTHENTICATIONTOKENSVUE"];

// Remove old structures
foreach ($oldStructs as $structName) {
    $structure = \Anakeen\Core\SEManager::getFamily($structName);
    if (!empty($structure)) {
        \Anakeen\Core\SmartStructure\DestroySmartStructure::destroyFamily($structName, true);
    }
}

// import new elements
$accountsAdmin = \Anakeen\Core\SEManager::getDocument("HGEA_ACCOUNTS");
if (empty($accountsAdmin)) {
    $import = new \Anakeen\Exchange\ImportDocument();
    $import->setPolicy("update");
    $import->setVerifyAttributeAccess(false);
    $cr = $import->importDocuments(\Anakeen\Core\ContextManager::getRootDirectory()."/vendor/Anakeen/AdminCenter/Config/hubConfiguration.xml", false);
}