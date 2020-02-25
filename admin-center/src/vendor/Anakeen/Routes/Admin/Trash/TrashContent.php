<?php


namespace Anakeen\Routes\Admin\Trash;

use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\Search\Internal\SearchSmartData;

class TrashContent
{

    /**
     * @param $seData
     * @return bool
     * @throws \Anakeen\Core\DocManager\Exception
     * @throws \Anakeen\Database\Exception
     */
    public function canDisplay($seData)
    {
        $se  = SEManager::getDocument($seData->id);
        if (!empty($se)) {
            $err = $se->control("view");
            return empty($err);
        }
        return false;
    }

    public function getAuthorName($seData)
    {
        $seId = $seData->id;
        $sql = <<<'SQL'
select distinct on(docread.initid) docread.*, dochisto.uname as deluser, dochisto.date as deldate  
from docread, dochisto 
where docread.id=%d and docread.id= dochisto.id and docread.doctype='Z' and dochisto.code = 'DELETE'
SQL;
        $result = [];
        DbManager::query(sprintf($sql, $seId), $result, false, true);
        return [
            "value" => $result["deluser"],
            "displayValue" => $result["deluser"]
        ];
    }
}
