<?php


//\Anakeen\LogManager::pushHandler(new \Monolog\Handler\StreamHandler("/tmp/hoho", \Anakeen\LogManager::getLogLevel()));
//\Anakeen\LogManager::pushHandler(new \Monolog\Handler\ErrorLogHandler(\Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, \Anakeen\LogManager::getLogLevel()));
//\Anakeen\LogManager::pushHandler(new \Monolog\Handler\ErrorLogHandler());

//\Anakeen\LogManager::setFormater(new \Monolog\Formatter\LineFormatter("%channel%{%level_name%}{%user%}: %message% %context% %extra%"));
\Anakeen\LogManager::error("Hohoo");
\Anakeen\LogManager::warning("Yeah", ["hello"=>"world"]);
\Anakeen\LogManager::debug("Hohoo");
\Anakeen\LogManager::info("Hohoo");
\Anakeen\LogManager::notice("Hohoo");
