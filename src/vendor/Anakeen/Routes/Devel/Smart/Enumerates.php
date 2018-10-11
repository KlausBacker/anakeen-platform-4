<?php

namespace Anakeen\Routes\Devel\Smart;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Ui\DataSource;

/**
 * Get All Enumerate Items
 *
 * @note    Used by route : GET /api/v2/devel/smart/enumerates/
 * Use request  parameters : ?take=50&skip=0&filter=<kendo filters>
 */
class Enumerates
{
    const ENUMPAGESIZE = 50;
    protected $enumName;
    protected $filters = [];
    protected $slice = self::ENUMPAGESIZE;
    protected $offset = 0;

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $this->enumName = $args["id"];
        $filters = $request->getQueryParam("filter");
        if ($filters) {
            $this->filters = DataSource::getFlatLevelFilters($filters);
        }
        if ($request->getQueryParam("take") === 'all') {
            $this->slice = $request->getQueryParam("take");
        } else {
            $this->slice = intval($request->getQueryParam("take", self::ENUMPAGESIZE));
        }
        $this->offset = intval($request->getQueryParam("skip", 0));
    }

    public function doRequest()
    {
        $data = [];
        $swhere = '';
        if ($this->filters) {
            $where = [];
            foreach ($this->filters as $filter) {
                $where[] = sprintf("%s ~* '%s'", pg_escape_identifier($filter["field"]), pg_escape_string($filter["value"]));
            }
            $swhere = "where " . implode(" and ", $where);
        }


        $sql = sprintf("select * from docenum %s order by name, eorder offset %d", $swhere, $this->offset);
        if ($this->slice !== 'all') {
            $sql = sprintf("select * from docenum %s order by name, eorder limit %d offset %d", $swhere, $this->slice, $this->offset);
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
        $requestData = ["offset" => $this->offset, "slice" => $this->slice];
        if ($this->filters) {
            $where = [];
            foreach ($this->filters as $filter) {
                $where[] = sprintf("%s contains '%s'", pg_escape_identifier($filter["field"]), pg_escape_string($filter["value"]));
            }
            $requestData["filter"] = implode(" and ", $where);
        }
        DbManager::query("select count(name) from docenum", $c, true, true);
        $requestData["total"] = intval($c);
        return $requestData;
    }
}
