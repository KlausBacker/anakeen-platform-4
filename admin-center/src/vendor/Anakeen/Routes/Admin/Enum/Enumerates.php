<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

class Enumerates
{

    private $take = 20;
    private $skip = 0;
    private $filterLogic = 'AND';
    private $filters = NULL; // [][]
    private $finalFilter = '';

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request);

        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function doRequest()
    {
 
       // Construct the filterable part of the query
        if(!is_null($this->filters)) {
            for($i = 0; $i < count($this->filters); $i++) {
                switch($this->filters[$i]["field"]){
                    case "enumerate":{
                        $this->finalFilter = $this->filterLogic." docenum.NAME LIKE('%".$this->filters[$i]["value"]."%') ";
                    }
                    break;
                    case "label":{
                        $this->finalFilter = $this->filterLogic." LABEL LIKE ('%".$this->filters[$i]["value"]."%') ";
                    }
                    break;
                    case "structures":{
                        $this->finalFilter = $this->filterLogic." LABELTEXT LIKE ('%".$this->filters[$i]["value"]."%') ";
                    }
                    break;
                    case "fields":{
                        $this->finalFilter = $this->filterLogic." TITLE LIKE ('%".$this->filters[$i]["value"]."%') ";
                    }
                    break;
                }
            }
            $this->filters = NULL;
        }
        $sqlPattern = <<<'SQL'
select docenum.key, docenum.name, docenum.label, docenum.disabled, docattr.docid, docattr.labeltext, docfam.title from docattr, docenum, docfam
where docattr.type='enum("'||docenum.name||'")' and docattr.docid = docfam.id %s ORDER BY docenum.name limit %s offset %s
SQL;

        $sql = sprintf($sqlPattern, $this->finalFilter, $this->take, $this->skip);

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
        $this->filterLogic = isset($param["filter"]) ? $param["filter"]["logic"] : "AND";
        $this->filters = isset($param["filter"]) ? $param["filter"]["filters"] : NULL;
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
where docattr.type='enum("'||docenum.name||'")' and docattr.docid = docfam.id %s
SQL;

        $sql = sprintf($sqlTotalQuery, $this->finalFilter);
        DbManager::query($sql, $total);
        return $total[0]["count"];
    }
}
