<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

class Enumerates
{

    private $take = 20;
    private $skip = 0;


    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request);

        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function doRequest()
    {

        $sqlPattern = <<<'SQL'
select docenum.key, docenum.name, docenum.label, docenum.disabled, docattr.docid, docattr.labeltext, docfam.title from docattr, docenum, docfam
where docattr.type='enum("'||docenum.name||'")' and docattr.docid = docfam.id  ORDER BY docenum.name
limit %s offset %s
SQL;

        $sql = sprintf($sqlPattern, $this->take, $this->skip);

        $extendableEnums = $this->getExtendableEnums();

        DbManager::query($sql, $enums);
        $data = array();
        foreach ($enums as $enum) {
            $entry["enumerate"] = $enum["name"];
            $entry["label"] = $enum["label"];
            $isModifiable = true;
            if (in_array($enum["name"], $extendableEnums)) {
                $isModifiable = false;
            }
            $entry["modifiable"] = $isModifiable;
            $entry["structures"] = $enum["labeltext"];
            $entry["fields"] = $enum["title"];
            array_push($data, $entry);
        }


        $result["data"] = $data;
        $result["debug"] = $sql;
        $result["total"] = $this->getTotal();

        return $result;
    }

    /**
     * parse paging, filtering and sorting parameters from request
     * @param \Slim\Http\request $request the request
     */
    private function parseParams(\Slim\Http\request $request)
    {
        $param = $request->getQueryParams();
        $this->take = isset($param["take"]) ? $param["take"] : 20;
        $this->skip = isset($param["skip"]) ? $param["skip"] : 0;
    }

    /**
     * Retrieves every enumerate which is extendable
     * An enumrate is extendable only if it has an entry with key ".extendable"
     */
    private function getExtendableEnums()
    {

        $extendableEnums = array();

        $sqlExtendable = <<<'SQL'
select name from docenum where key='.extendable'
SQL;

        DbManager::query($sqlExtendable, $extendables);
        foreach ($extendables as $enumName) {
            array_push($extendableEnums, $enumName["name"]);
        }

        return $extendableEnums;
    }

    /**
     * @return mixed
     * @throws \Anakeen\Database\Exception
     */
    private function getTotal()
    {
        $sqlTotalQuery = <<<'SQL'
select count(docenum.name) from docattr, docenum, docfam
where docattr.type='enum("'||docenum.name||'")' and docattr.docid = docfam.id
SQL;
        DbManager::query($sqlTotalQuery, $total);
        return $total[0]["count"];
    }
}
