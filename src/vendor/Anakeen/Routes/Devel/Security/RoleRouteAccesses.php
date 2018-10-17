<?php

namespace Anakeen\Routes\Devel\Security;

use Anakeen\Core\DbManager;
use Anakeen\Routes\Devel\GridFiltering;

/**
 * Get Right Accesses
 *
 * @note Used by route : GET /api/v2/devel/security/routes/accesses/
 */
class RoleRouteAccesses extends GridFiltering
{

    protected $sWhere;

    public function doRequest()
    {
        $data = parent::doRequest();

        $sql = <<< SQL
select acl.name as accessname, users.login, users.accounttype from permission, acl, users where computed is null and acl.id = permission.id_acl and users.id = permission.id_user
SQL;

        $this->sWhere = $this->getSqlWhere();
        $sql .= ' ' . $this->sWhere;

        if ($this->slice !== 'all') {
            $sql .= sprintf(" limit %d offset %d", $this->slice, $this->offset);
        }

        DbManager::query($sql, $results);
        $access = [];
        foreach ($results as $result) {
            list($accessNs, $accessName) = explode("::", $result["accessname"]);
            $access[] = [
                "accessNs" => $accessNs,
                "accessName" => $accessName,
                "account" => [
                    "reference" => $result["login"],
                    "type" => Profile::getAccountType($result["accounttype"])
                ]
            ];
        }

        $data["access"] = $access;
        return $data;
    }

    protected function getSqlWhere()
    {
        if ($this->filters) {
            $where = [];
            foreach ($this->filters as $filter) {
                $colFilter = $filter["field"];
                $value = pg_escape_string(preg_quote($filter["value"]));
                switch ($colFilter) {
                    case "accessNs":
                        $where[] = sprintf("acl.name ~* '^%s'", $value);
                        break;
                    case "accessName":
                        $where[] = sprintf("acl.name ~* '::%s'", $value);
                        break;
                    case "account":
                        $where[] = sprintf("login ~* '%s'", $value);
                        break;
                }
            }
            return "where " . implode(" and ", $where);
        }
        return "";
    }


    protected function getRequestParameters()
    {
        $requestData = parent::getRequestParameters();
        DbManager::query("select count(*) from permission where computed is null", $c, true, true);
        $requestData["total"] = intval($c);
        return $requestData;
    }
}
