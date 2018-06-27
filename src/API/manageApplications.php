<?php
/**
 * Add, modify or delete application
 *
 *
 * @param string $appname internal name of the application
 * @param string $method  may be "init","reinit","update","delete"
 *
 * @subpackage WSH
 */

$usage = new \Anakeen\Script\ApiUsage();
$usage->setDefinitionText("Manage application");
$appname = $usage->addRequiredParameter("appname", "application name");
$method = $usage->addOptionalParameter("method", "action to do", array(
    "init",
    "update",
    "reinit",
    "delete"
), "init");


$usage->verify();

echo " $appname...$method\n";


$routeConfig = \Anakeen\Router\RouterLib::getRouterConfig();
$apps = $routeConfig->getApps();

if (!empty($apps[$appname])) {
    switch ($method) {
        case "init":
        case "update":
            $apps[$appname]->record();
            break;

        case "reinit":
            $apps[$appname]->record();
            // @TODO
            break;

        case "delete":
            $app = new \Anakeen\Core\Internal\Application();
            $app->set($appname, $Null, null, false);
            if ($app->isAffected()) {
                $err = $app->DeleteApp();
                if ($err != '') {
                    \Anakeen\Core\ContextManager::exitError($err);
                }
            } else {
                echo "already deleted";
            }
            break;
    }

    $accesses = $routeConfig->getAccesses();
    foreach ($accesses as $access) {
        if ($access->applicationContext === $appname) {
            $access->record();
        }
    }
} else {
    throw new \Anakeen\Script\Exception(sprintf('App "%s" not found', $appname));
}