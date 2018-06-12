<?php

$usage = new \Anakeen\Script\ApiUsage();

$usage->setDefinitionText("Update account CVDOC");
$usage->verify();

try {
    \Anakeen\Core\DbManager::query("update family.iuser set cvid=(select id from family.cvdoc where name='CV_IUSER_ACCOUNT');");
    \Anakeen\Core\DbManager::query("update family.igroup set cvid=(select id from family.cvdoc where name='CV_GROUP_ACCOUNT');");
    echo "Migration done !\n";
} catch (\Dcp\Db\Exception $e) {
    echo "Unable to migrate ".$e->getMessage()."\n";
}
