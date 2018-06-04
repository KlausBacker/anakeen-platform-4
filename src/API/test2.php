<?php

$a=new Mustache_Engine();
$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Get tittle");
$docid = $usage->addRequiredParameter("docid", "special docid");

$usage->verify();
$a=\Anakeen\Core\SEManager::getDocument($docid);


$a->setValue("ba_desc", date("Y-m-d H:M:i"));
$a->store();
print $a->getTitle();
print "\n";
