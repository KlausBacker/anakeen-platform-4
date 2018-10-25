<?php

namespace Anakeen\Routes\Migration\Database;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Migration\Utils;
use Anakeen\Router\ApiV2Response;
use Anakeen\Router\Exception;

/**
 * Class ConfigApplicationTransfert
 * @package Anakeen\Routes\Migration\Database
 * @use by route POST /api/v2/migration/database/transfert/application/{application}
 */
class ConfigApplicationTransfert
{
    protected $applicationName;
    protected $structure;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function initParameters($args)
    {
        $this->applicationName = $args["application"];

        Utils::importForeignTable('application');
        $sql = sprintf("select id from dynacase.application where name='%s'", pg_escape_string($this->applicationName));
        DbManager::query($sql, $idapp, true, true);
        if (!$idapp) {
            throw new Exception(sprintf("Application \"%s\" not found", $this->applicationName));
        }
    }

    protected function doRequest()
    {
        $data = [];

        // Transferring acl
        $aclIds = $this->transfertAcls($this->applicationName);

        //Transferring parameters
        $paramIds = $this->transfertParameters($this->applicationName);
        $actionIds = $this->transfertActions($this->applicationName);

        $data["countAcl"] = count($aclIds);
        $data["countParam"] = count($paramIds);
        $data["countAction"] = count($actionIds);
        return $data;
    }

    protected static function transfertActions($appName)
    {
        Utils::importForeignTable('action');

        $dsql = <<<SQL
select application.name as appname, action.name, action.short_name, action.short_name, action.script, action.function, action.layout, action.acl 
from dynacase.action as action, dynacase.application as application 
where action.id_application = application.id and application.name = '%s';
SQL;
        $sql = sprintf($dsql, pg_escape_string($appName));
        DbManager::query($sql, $results);

        $template = file_get_contents(__DIR__ . '/../../../Migration/RouteApp.xml.mustache');
        $routeConfigPath = sprintf("%s/%s/%s/app.xml", ContextManager::getRootDirectory(), \Anakeen\Core\Settings::RouterConfigDir, $appName);
        $data["APPNAME"] = $appName;
        $data["VENDOR"] = ContextManager::getParameterValue("Migration", "VENDOR");
        $data["actions"] = $results;

        $mustache = new \Mustache_Engine();
        $routeConfigContent = $mustache->render($template, $data);
        Utils::writeFileContent($routeConfigPath, $routeConfigContent);


        $stubTemplateAction = file_get_contents(__DIR__ . '/../../../Migration/AppRoute.php.mustache');

        // Write Route PHP class stubs : one by action
        foreach ($results as $result) {
            $routeAppPath = sprintf(
                "%s/vendor/%s/Routes/Apps/%s/%s.php",
                ContextManager::getRootDirectory(),
                $data["VENDOR"],
                $appName,
                $result["name"]
            );
            $result["VENDOR"] = $data["VENDOR"];
            $result["APPNAME"] = $data["APPNAME"];
            //print "$routeAppPath\n";

            $routeConfigContent = $mustache->render($stubTemplateAction, $result);
            Utils::writeFileContent($routeAppPath, $routeConfigContent);
        }

        //print "$routeConfigPath\n";
        return $results;
    }

    protected static function transfertAcls($appName)
    {
        Utils::importForeignTable('acl');


        $dsql = <<<SQL
        insert into acl(name, id, description, group_default)
select dapp.name || '::' || dacl.name as name, dacl.id, dacl.description, dacl.group_default 
from dynacase.acl as dacl, dynacase.application as dapp 
where dacl.id_application = dapp.id and dapp.name='%s' and dacl.id not in (select id from acl)
returning id;
SQL;

        $sql = sprintf($dsql, pg_escape_string($appName));
        DbManager::query($sql, $ids);

        $sql = sprintf("select * from acl where name ~ '^%s::'", pg_escape_string($appName));
        DbManager::query($sql, $results);


        $template = file_get_contents(__DIR__ . '/../../../Migration/RouteAccesses.xml.mustache');
        $routeConfigPath = sprintf("%s/%s/%s/accesses.xml", ContextManager::getRootDirectory(), \Anakeen\Core\Settings::RouterConfigDir, $appName);
        $data["APPNAME"] = $appName;
        $data["VENDOR"] = ContextManager::getParameterValue("Migration", "VENDOR");
        $data["acls"] = $results;

        $mustache = new \Mustache_Engine();
        $routeConfigContent = $mustache->render($template, $data);
        Utils::writeFileContent($routeConfigPath, $routeConfigContent);

        return $ids;
    }

    protected function transfertParameters($appName)
    {
        Utils::importForeignTable('paramv');
        Utils::importForeignTable('paramdef');

        $dsql = <<<SQL
        insert into paramdef(name, isuser, descr, kind)
select * from (select dapp.name || '::' || dpdef.name as name, dpdef.isuser, dpdef.descr, dpdef.kind 
from dynacase.paramdef as dpdef, dynacase.application as dapp 
where dpdef.appid = dapp.id and dapp.name='%s' and dpdef.name <> 'APPNAME') as z
where z.name not in (select name from paramdef)
returning name;
SQL;

        $sql = sprintf($dsql, pg_escape_string($appName));
        DbManager::query($sql, $ids);


        $dsql = <<<SQL
        insert into paramv(name, type, val)
select * from (select dapp.name || '::' || dparamv.name as name, dparamv."type", dparamv.val
from dynacase.paramv as dparamv, dynacase.application as dapp 
where dparamv.appid = dapp.id and dapp.name='%s' and dparamv.name <> 'APPNAME' and dparamv.name <> 'VERSION') as z
where z.name not in (select name from paramv)
returning name;
SQL;

        $sql = sprintf($dsql, pg_escape_string($appName));
        DbManager::query($sql, $ids);

        DbManager::query("update paramv set type='G' where type='A'");

        return $ids;
    }
}
