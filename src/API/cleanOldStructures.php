<?php

use Anakeen\Core\SEManager;
use Anakeen\Core\SmartStructure\DestroySmartStructure;

$oldStructs = ["HUBTEMANAGERVUE"];

// Remove old structures
foreach ($oldStructs as $structName) {
    $structure = SEManager::getFamily($structName);
    if (!empty($structure)) {
        DestroySmartStructure::destroyFamily($structName, true);
    }
}
