<?php

\Anakeen\Core\DbManager::query("select name from docfam order by name", $names, true);
$pval = [];
foreach ($names as $name) {
    $pval[] = ["name" => $name];
}
\Anakeen\Core\ContextManager::setParameterValue("BasicShowcase", "BASIC_SHOWCASE_COLLECTIONS", json_encode($pval));
