<?php

$a=new Mustache_Engine();
$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Get tittle");
$docid = $usage->addRequiredParameter("docid", "special docid");

$usage->verify();
$a=\Anakeen\Core\SEManager::getDocument($docid);


$a->refreshTitle();
print $a->getTitle();
print "\n";

print \tit\toto::class;
print \Anakeen\Core\Internal\Autoloader::classExists("\Anakeen\Core\\Internal\\Action");
print \Anakeen\Core\Internal\Autoloader::classExists("Anakeen\Core\\Utils\\FileMime");

print "\n";