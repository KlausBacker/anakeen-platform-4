#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/Anakeen/autoload.php';

//
//$p = new \Anakeen\Core\Account();
//\Anakeen\Core\DbManager::query($p->sqlcreate);

$p = new \Anakeen\Core\Internal\Param();
\Anakeen\Core\DbManager::query($p->sqlcreate);
