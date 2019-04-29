<?php

$oldStructs = ["HUBADMINCENTERACCOUNTSVUE", "HUBADMINCENTERPARAMETERSVUE", "HUBADMINCENTERVAULTVUE", "HUBAUTHENTICATIONTOKENSVUE"];

// Remove old structures
foreach ($oldStructs as $structName) {
    $structure = \Anakeen\Core\SEManager::getFamily($structName);
    if (!empty($structure)) {
        \Anakeen\Core\SmartStructure\DestroySmartStructure::destroyFamily($structName, true);
    }
}