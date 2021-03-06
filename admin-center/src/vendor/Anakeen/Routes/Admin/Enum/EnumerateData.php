<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Core\EnumManager;
use Anakeen\Router\ApiV2Response;
use String\sprintf;

class EnumerateData
{
    private $id = '';
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request, $args);
        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function doRequest()
    {
        $sqlPattern = <<<'SQL'
select docenum.key, docenum.label, docenum.disabled, docenum.eorder from docenum where name = '%s' and key != '.extendable' ORDER BY docenum.eorder
SQL;

        $sql = sprintf($sqlPattern, pg_escape_string($this->id));
        DbManager::query($sql, $enumData);

        $result = array();

        foreach ($enumData as $enumEntry) {
            $temp["key"] = $enumEntry["key"];
            $temp["label"] = $enumEntry["label"];
            $temp["active"] = $enumEntry["disabled"] === "t" ? "disable" : "enable";
            $temp["eorder"] = $enumEntry["eorder"];
            array_push($result, $temp);
        }
        return $result;
    }

    private function parseParams(\Slim\Http\request $request, $args)
    {
        $this->id = $args["id"];
    }
}
