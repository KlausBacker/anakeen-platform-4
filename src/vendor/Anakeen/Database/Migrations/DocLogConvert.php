<?php

namespace Anakeen\Database\Migrations;

use Anakeen\Core\Internal\QueryDb;

class DocLogConvert
{
    public function __invoke()
    {
        $q = new QueryDb("", \DocLog::class);
        /** @var \resource $logs */
        $q->basic_elem->sup_fields[] = "arg";
        $q->addQuery("arg is not null");
        $logs = $q->query(0, 0, "ITEM");
        $doclog = new \DocLog();
        for ($c = 0; $c < $q->nb; $c++) {
            $logValues = pg_fetch_array($logs, $c, PGSQL_ASSOC);
            $logValues["data"] = json_encode(unserialize($logValues["arg"]));
            $doclog->affect($logValues);

            $doclog->modify();
        }
    }
}
