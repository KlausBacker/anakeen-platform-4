<?php


namespace Anakeen\Routes\Admin\Enum;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

class Enumerates
{

    private $take = 20;
    private $skip = 0;
    private $filterLogic = 'AND';
    private $filters = null;
    private $finalFilter = '';
    private $sortingDirection = '';
    private $sortingField = null;
    private $finalSorting = 'ORDER BY docenum.name';

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->parseParams($request);

        return ApiV2Response::withData($response, $this->doRequest());
    }

    protected function doRequest()
    {
        // Construct filterable's part of the query
        if (!is_null($this->filters)) {
            for ($i = 0; $i < count($this->filters); $i++) {
                switch ($this->filters[$i]["field"]) {
                    case 'enumerate':
                        $this->finalFilter = pg_escape_string($this->filterLogic) . " docenum.NAME LIKE('%" . pg_escape_string($this->filters[$i]["value"]) . "%') ";
                        break;
                    case 'label':
                        $this->finalFilter = pg_escape_string($this->filterLogic) . " LABEL LIKE ('%" . pg_escape_string($this->filters[$i]["value"]) . "%') ";
                        break;
                    case 'structures':
                        $this->finalFilter = pg_escape_string($this->filterLogic) . " LABELTEXT LIKE ('%" . pg_escape_string($this->filters[$i]["value"]) . "%') ";
                        break;
                    case 'fields':
                        $this->finalFilter = pg_escape_string($this->filterLogic) . " TITLE LIKE ('%" . pg_escape_string($this->filters[$i]["value"]) . "%') ";
                        break;
                }
            }
            $this->filters = null;
        }
        // Construct sortable's part of the query
        if (!is_null($this->sortingField)) {
            switch ($this->sortingField) {
                case 'enumerate':
                    $this->finalSorting = "ORDER BY docenum.name " . pg_escape_string($this->sortingDirection);
                    break;
                case 'label':
                    $this->finalSorting = "ORDER BY docenum.label " . pg_escape_string($this->sortingDirection);
                    break;
                case 'structures':
                    $this->finalSorting = "ORDER BY labeltext " . pg_escape_string($this->sortingDirection);
                    break;
                case 'fields':
                    $this->finalSorting = "ORDER BY title " . pg_escape_string($this->sortingDirection);
                    break;
            }
        }
        $sqlPattern = <<<'SQL'
select docenum.key, docenum.name, docenum.label, docenum.disabled, docattr.docid, docattr.labeltext, docfam.title from docattr, docenum, docfam
where docattr.type='enum("'||docenum.name||'")' and docattr.docid = docfam.id %s %s limit %s offset %s
SQL;

        $sql = sprintf($sqlPattern, $this->finalFilter, $this->finalSorting, pg_escape_string($this->take), pg_escape_string($this->skip));

        $extendableEnums = $this->getExtendableEnums();

        DbManager::query($sql, $enums);
        $data = array();
        foreach ($enums as $enum) {
            $entry["enumerate"] = $enum["name"];
            $entry["label"] = $enum["label"];
            $isModifiable = false;
            if (in_array($enum["name"], $extendableEnums)) {
                $isModifiable = true;
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
        $this->filters = isset($param["filter"]) ? $param["filter"]["filters"] : null;
        $this->sortingField = isset($param["sort"]) ? $param["sort"][0]["field"] : $this->sortingField;
        $this->sortingDirection = isset($param["sort"]) ? $param["sort"][0]["dir"] : $this->sortingDirection;
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
