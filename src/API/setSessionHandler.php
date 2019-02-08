<?php

use Anakeen\Core\ContextManager;

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("add session handler");
$handlerName = $usage->addRequiredParameter("handlerClass", "class name of session handler to use - set to SessionHandler to use php system handler");
$usage->verify();

$handlerCode = '';
if ($handlerName != "SessionHandler") {
    if (!class_exists($handlerName)) {
        ContextManager::exitError(sprintf("class handler %s not found", $handlerName));
    }
    $ref = new ReflectionClass($handlerName);
    $filePath = $ref->getFileName();

    if (strpos($filePath, DEFAULT_PUBDIR) == 0) {
        $basefilePath = substr($filePath, strlen(DEFAULT_PUBDIR) + 1);
        if (file_exists($basefilePath)) {
            $filePath = $basefilePath;
        }
    }
    $h = new $handlerName();

    $handlerCode = sprintf('<?php require_once("%s");$handler = new %s();session_set_save_handler($handler, true);', $filePath, $handlerName);

    file_put_contents("config/sessionHandler.php", $handlerCode);
    printf("Write config/sessionHandler.php Done.\n");
} else {
    $handlerCode = '';
    file_put_contents("config/sessionHandler.php", $handlerCode);
    printf("Reset config/sessionHandler.php Done.\n");
}
