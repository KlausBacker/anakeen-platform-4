<?php

namespace Anakeen\Routes\Devel;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;
use Anakeen\Ui\DataSource;

/**
 * Get Right Accesses
 *
 * @note Used by route : GET api/v2/devel/security/profile/{id}/accesses/
 */
class GridFiltering
{

    const PAGESIZE = 50;
    protected $filters = [];
    protected $slice = self::PAGESIZE;
    protected $offset = 0;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function initParameters(\Slim\Http\request $request, $args)
    {
        $filters = $request->getQueryParam("filter");
        if ($filters) {
            $this->filters = DataSource::getFlatLevelFilters($filters);
        }
        if ($request->getQueryParam("take") === 'all') {
            $this->slice = $request->getQueryParam("take");
        } else {
            $this->slice = intval($request->getQueryParam("take", self::PAGESIZE));
        }
        $this->offset = intval($request->getQueryParam("skip", 0));
    }


    public function doRequest()
    {
        $data["requestParameters"] = $this->getRequestParameters();

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

        return $requestData;
    }

    protected function getSqlWhere()
    {
        if ($this->filters) {
            $where = [];
            foreach ($this->filters as $filter) {
                if (pg_escape_string($filter["value"]) === "true") {
                    $where[] = sprintf("%s", pg_escape_identifier($filter["field"]));
                } elseif (pg_escape_string($filter["value"]) === "false") {
                    $where[] = sprintf("%s is null", pg_escape_identifier($filter["field"]));
                } else {
                    $where[] = sprintf("%s ~* '%s'", pg_escape_identifier($filter["field"]), pg_escape_string($filter["value"]));
                }
            }
            return "where " . implode(" and ", $where);
        }
        return "";
    }
}
