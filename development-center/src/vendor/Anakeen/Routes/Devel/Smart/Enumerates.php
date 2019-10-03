<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\DbManager;
use Anakeen\Core\EnumManager;
use Anakeen\Routes\Devel\GridFiltering;

/**
 * Get All Enumerate Items
 *
 * @note Used by route : GET /api/v2/devel/smart/enumerates/
 * Use request  parameters : ?take=50&skip=0&filter=<kendo filters>
 */
class Enumerates extends GridFiltering
{
    protected $sWhere = "";

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        parent::initParameters($request, $args);
    }

    public function doRequest()
    {
        $data = [];

        $this->sWhere = $this->getSqlWhere();

        $sql = sprintf(
            "select * from docenum %s and key != '%s' order by name, eorder offset %d",
            $this->sWhere,
            EnumManager::EXTENDABLEKEY,
            $this->offset
        );
        if ($this->slice !== 'all') {
            $sql = sprintf(
                "select * from docenum %s and key != '%s' order by name, eorder limit %d offset %d",
                $this->sWhere,
                EnumManager::EXTENDABLEKEY,
                $this->slice,
                $this->offset
            );
        }
        DbManager::query($sql, $results);
        foreach ($results as &$result) {
            $result["eorder"] = intval($result["eorder"]);
            $result["disabled"] = ($result["disabled"] === 't');
        }

        $data["requestParameters"] = $this->getRequestParameters();
        $data["enumerates"] = $results;
        return $data;
    }


    protected function getRequestParameters()
    {
        $requestData = parent::getRequestParameters();
        DbManager::query(
            sprintf("select count(name) from docenum {$this->sWhere} and key != '%s'", EnumManager::EXTENDABLEKEY),
            $c,
            true,
            true
        );
        $requestData["total"] = intval($c);
        return $requestData;
    }
}
