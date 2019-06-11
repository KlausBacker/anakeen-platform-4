#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/Anakeen/autoload.php';

$paramName = "Core::CORE_TMPDIR";

\Anakeen\LogManager::warning(sprintf("Restoring default value for parameter '%s'.", $paramName));
\Anakeen\Core\DbManager::query(sprintf('UPDATE paramv SET val = %s WHERE name = %s', pg_escape_literal('./var/tmp'), pg_escape_literal($paramName)));


$tmpDir=__DIR__.'/../var/tmp';
if (! is_dir($tmpDir)) {
    mkdir($tmpDir);
}


$cacheDir=__DIR__.'/../'.\Anakeen\Core\Settings::CacheDir;
if (! is_dir($cacheDir)) {
    mkdir($cacheDir);
    mkdir($cacheDir."/image");
    mkdir($cacheDir."/file");
}

$sessionDir=__DIR__.'/../'.\Anakeen\Core\Internal\Session::SESSION_SUBDIR;
if (! is_dir($sessionDir)) {
    mkdir($sessionDir);
}
