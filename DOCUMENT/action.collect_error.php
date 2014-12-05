<?php

function collect_error(Action &$action) {
    $requestData = json_decode(file_get_contents('php://input'), true);
    $loggerList = json_decode(ApplicationParameterManager::getParameterValue("DOCUMENT", "LOGGER"));
    if (json_last_error()) {
        throw new Exception("Unable to decode the log handler list");
    }
    $message = isset($requestData["message"]) ? $requestData["message"] : "";
    $context = isset($requestData["url"]) ? "## URL : ".$requestData["url"] : "";
    $context .= " ".(isset($requestData["useragent"]) ? "## BrowserUserAgent : ".$requestData["useragent"] : "");
    $context .= " ##User : " . Account::getDisplayName($action->user->id) . "(#".$action->user->id.")";
    $stack = isset($requestData["stack"]) ? print_r($requestData["stack"], true) : "";
    foreach($loggerList as $currentLogger) {
        $logger = new $currentLogger();
        /* @var $logger \Dcp\UI\Logger\JS\Logger */
        $logger->writeError($message, $context, $stack);
    }
}