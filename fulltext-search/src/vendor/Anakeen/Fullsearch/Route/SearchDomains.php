<?php

namespace Anakeen\Fullsearch\Route;

use Anakeen\Core\DbManager;
use Anakeen\Fullsearch\FileContentDatabase;
use Anakeen\Fullsearch\SearchDomainDatabase;
use Anakeen\Fullsearch\SearchDomainManager;
use Anakeen\Router\ApiV2Response;
use SmartStructure\Fields\Task as TaskFields;

class SearchDomains
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $data = $this->doRequest();
        return ApiV2Response::withData($response, $data);
    }

    protected function doRequest()
    {
        $data = [];

        $data["config"] = array_values(SearchDomainManager::getConfig());

        foreach ($data["config"] as &$config) {
            $config["database"] = $this->getDbSize($config["name"]);
        }
        $data["fileCacheSize"] = $this->getDbTableSize(FileContentDatabase::DBTABLE);
        $data["nextUpdateDate"]=$this->getNextDateForAutoUpdate();

        return $data;
    }

    protected function getDbSize($domainName)
    {
        $db = new SearchDomainDatabase($domainName);

        $data = $db->getDbStats();
        $data["size"] = $this->getDbTableSize($db->getTableName());


        return $data;
    }


    protected function getDbTableSize($tableName)
    {
        $sql = <<<SQL
        SELECT 
        pg_total_relation_size('%s') AS "size",
        pg_size_pretty(pg_total_relation_size('%s')) AS "prettySize" ;
SQL;
        $sql = sprintf($sql, pg_escape_string($tableName), pg_escape_string($tableName));

        DbManager::query($sql, $size, false, true);
        return $size;
    }

    protected function getNextDateForAutoUpdate() {
        $s=new \Anakeen\Search\SearchElements("TASK");
        $s->addFilter("%s = 'FullSearch'", TaskFields::task_route_ns);
        $s->addFilter("%s = 'UpdateSearchData'", TaskFields::task_route_name);
        $s->addFilter("%s = 'active'", TaskFields::task_status);
        $s->search();
        $tasks=$s->getResults();
        $dates=[];
        foreach ($tasks as $task) {
            $dates[]=$task->getRawValue(TaskFields::task_nextdate);
        }

        return min($dates);
    }
}
