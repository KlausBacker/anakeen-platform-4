<?php

namespace Anakeen\Routes\Admin\Account;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

/**
 * Get group data to be used by AnkTree widget
 * @note use by route GET /api/v2/admin/account/grouptree/
 * @note use by route GET /api/v2/admin/account/grouptree/groupid
 */
class GroupTree
{
    /**
     * @var array
     */
    protected $results = [];
    protected $parentId = 0;
    protected $filterValue = "";
    protected $expandAll = false;
    protected $prefix = "";
    /**
     * @var array
     */
    protected $userCounts = [];
    protected $groupCounts = [];
    protected $needCounts = true;
    protected $positionIndex = 0;
    protected $directGroupCounts = [];
    protected $filterMatchOnly = false;


    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param string[] $args
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->setParameters($request, $args);

        $etag = $this->getEtag();
        if ($etag) {
            $response = ApiV2Response::withEtag($request, $response, $etag);
            if (ApiV2Response::matchEtag($request, $etag)) {
                return $response;
            }
        }

        $response = ApiV2Response::withData($response, $this->doProcess());
        if (empty($data["error"]) && $etag) {
            $response = ApiV2Response::withEtag($request, $response, $etag);
        }
        return $response;
    }

    protected function setParameters(\Slim\Http\request $request, $args)
    {
        $this->filterValue = $request->getQueryParam("filter");
        $this->filterMatchOnly = $request->getQueryParam("filterMatchOnly") === "true";
        $this->needCounts = json_decode($request->getQueryParam("getCounts"), true);

        $groupId = $args["groupid"] ?? 0;
        if (!empty($args["all"]) || ($this->filterValue && $groupId === 0)) {
            $this->expandAll = true;
        } else {
            $this->parentId = $groupId;
        }
    }


    protected function doProcess()
    {
        $this->addGroupSqlView();
        $this->prefix = uniqid("i");

        if ($this->expandAll === true) {
            $data = $this->getTreeAllData();
        } else {
            $data = $this->getBranchData();
        }
        return $data;
    }

    /**
     * View used as reference for group data
     * @throws \Anakeen\Database\Exception
     */
    protected function addGroupSqlView()
    {
        $view = "create temporary view tv_groups as select id, accounttype, fid, memberof, lastname, login from users where users.accounttype = 'G';";

        DbManager::query($view);
    }

    /**
     * Recompose array to delete pointer link
     * Add items and children counts if needed
     * @param array $values
     * @return array
     */
    protected function completeChildrenValue(array $values)
    {
        $nv = [];
        foreach ($values as $k => $v) {
            $v["directChildsCount"] = $this->directGroupCounts[$v["id"]] ?? 0;
            $v["index"] = $this->prefix . ($this->positionIndex++);
            if (!empty($v["children"])) {
                $v["loadedChildrenCount"] = count($v["children"]);
                if ($this->expandAll === false) {
                    unset($v["children"]);
                } else {
                    $v["children"] = $this->completechildrenValue($v["children"]);
                }
            } else {
                if ($this->expandAll === false) {
                    //$v["children"]= $v["directChildsCount"];
                    $v["loadedChildrenCount"] = $v["directChildsCount"];
                } else {
                    $v["loadedChildrenCount"] = 0;
                }
            }
            if ($this->needCounts["item"] === true) {
                $v["itemCount"] = $this->userCounts[$v["id"]] ?? 0;
            }
            if ($this->needCounts["children"] === true) {
                $v["childrenCount"] = $this->groupCounts[$v["id"]] ?? 0;
            }

            $nv[] = $v;
        }

        return $nv;
    }

    /**
     * Get tree data for only single branch without including children
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    protected function getBranchData()
    {
        $pointerTree = [];

        if ($this->parentId === 0) {
            DbManager::query(
                "
select id, login, fid as accountid, lastname as name from tv_groups
             where not exists (select iduser from groups, users where iduser = tv_groups.id and groups.idgroup = users.id and users.accounttype='G') order by lastname, id;
                ",
                $allGroupInfo
            );
        } else {
            DbManager::query(
                sprintf(
                    "select tv_groups.id, tv_groups.login, tv_groups.fid as accountid, tv_groups.lastname as name from tv_groups, groups" .
                    " where idgroup = %d and groups.iduser = tv_groups.id order by lastname, id;",
                    $this->parentId
                ),
                $allGroupInfo
            );
        }

        if ($this->filterValue) {
            $matchesGroup = $this->getMatchedGroup();
            if (count($matchesGroup) > 0) {
                $matchesGroupIds = [];
                foreach ($matchesGroup as $match) {
                    $matchesGroupIds[$match["id"]] = $match["id"];
                }
                foreach ($allGroupInfo as $kg => $groupInfo) {
                    if (!empty($matchesGroupIds[$groupInfo["id"]])) {
                        $allGroupInfo[$kg]["match"] = true;
                    } elseif ($this->filterMatchOnly === false) {
                        unset($allGroupInfo[$kg]);
                    }
                }
            }
        }

        foreach ($allGroupInfo as $groupInfo) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $id = intval($groupInfo["id"]);
            $result[$id] = $groupInfo;
            $pointerTree[$id] = &$result[$id];
        }

        $this->insertCounts(array_keys($pointerTree));

        $this->results = $this->completeChildrenValue($pointerTree);


        return [
            "treeData" => $this->results,
            "filter" => $this->filterValue
        ];
    }


    /**
     * Get all tree data including children
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    protected function getTreeAllData()
    {

        // Get top groups

        DbManager::query(
            "
            select id 
             from tv_groups where accounttype='G' and id not in 
            (select iduser from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G');
            ",
            $topGroupIds,
            true
        );

        $pointerTree = [];

        if ($this->filterValue) {
            $matchesGroup = $this->getMatchedGroup();
            if (count($matchesGroup) > 0) {
                $matchesGroupIds = [];
                foreach ($matchesGroup as &$match) {
                    $matchesGroupIds[$match["id"]] = $match["id"];
                    $match["match"] = true;
                }
                // get all ancestor of matched groups
                $sql = sprintf(
                    "with recursive agroups(gid) as (
 select idgroup from groups,users where iduser in (%s) and users.id=groups.idgroup
union
 select idgroup from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.idgroup
) select users.id, users.login, users.fid as accountid, users.lastname as name from agroups, users where users.id=agroups.gid and users.accounttype='G' order by lastname, id",
                    implode(",", $matchesGroupIds)
                );
                DbManager::query(
                    $sql,
                    $allGroupInfo
                );
                foreach ($allGroupInfo as &$matchInfo) {
                    $matchInfo["match"] = isset($matchesGroupIds[$matchInfo["id"]]);
                }
                $allGroupInfo = array_merge($matchesGroup, $allGroupInfo);
            } else {
                $allGroupInfo = [];
            }
        } else {
            DbManager::query(
                "select tv_groups.id, tv_groups.login, tv_groups.fid as accountid, tv_groups.lastname as name " .
                "from tv_groups order by lastname,id;",
                $allGroupInfo
            );
        }


        if (count($allGroupInfo) > 0) {
            foreach ($allGroupInfo as $groupInfo) {
                /* @var $currentAccount \Anakeen\Core\Account */
                $id = intval($groupInfo["id"]);
                $result[$id] = $groupInfo;
                $pointerTree[$id] = &$result[$id];
            }


            if ($this->filterValue) {
                $groupIds = array_keys($pointerTree);
                DbManager::query(
                    sprintf(
                        "select groups.* from users gu, users uu, groups " .
                        "where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G' and gu.id in (%s) order by uu.lastname, uu.login;",
                        implode(",", $groupIds)
                    ),
                    $groupBranches
                );
            } else {
                // Get all group branches
                $groupIds = [-1];
                $groupBranches = $this->getAllGroupBranches();
            }

            // Compose the tree from branches
            foreach ($groupBranches as $branch) {
                $parent = intval($branch["idgroup"]);
                $child = intval($branch["iduser"]);

                if (isset($pointerTree[$parent]) && isset($pointerTree[$child])) {
                    $pointerTree[$parent]["children"][$child] =  &$pointerTree[$child];
                }
            }

            // Delete no top branches into the tree
            $topIdx = [];
            foreach ($topGroupIds as $idx) {
                $topIdx[$idx] = true;
            }

            foreach ($pointerTree as $accountId => $info) {
                if (isset($topIdx[$accountId]) === false) {
                    unset($pointerTree[$accountId]);
                }
            }


            $this->insertCounts($groupIds);
            $this->results = $this->completechildrenValue($pointerTree);


            return [
                "treeData" => $this->results,
                "filter" => $this->filterValue,
                "hasChilds" => true
            ];
        } else {
            return [
                "treeData" => [],
                "message" => sprintf(___("No group name match \"%s\".", "igroup"), $this->filterValue)
            ];
        }
    }

    protected function getMatchedGroup()
    {
        $filtersWords = explode(" ", $this->filterValue);
        $wheres = [];
        foreach ($filtersWords as $filtersWord) {
            $filtersWord = trim($filtersWord);
            if ($filtersWord) {
                $wheres[] = sprintf("unaccent(lastname) ~* unaccent('%s')", pg_escape_string($filtersWord));
            }
        }
        $matchesGroup = [];
        if (count($wheres) > 0) {
            DbManager::query(
                sprintf(
                    "select tv_groups.id, tv_groups.login, tv_groups.fid as accountid, tv_groups.lastname as name " .
                    "from tv_groups where  %s;",
                    implode(" and ", $wheres)
                ),
                $matchesGroup
            );
        }
        return $matchesGroup;
    }

    protected function getAllGroupBranches()
    {
        // Get all group branches
        DbManager::query(
            "select groups.* from users gu, tv_groups uu, groups " .
            "where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G' order by uu.lastname, uu.login;",
            $groupBranches
        );
        return $groupBranches;
    }

    protected function insertCounts($ids)
    {
        $this->directGroupCounts = $this->insertDirectGroupCountToNode($ids);
        if ($this->needCounts["item"]) {
            $this->userCounts = $this->insertUserCountToNode($ids);
        }
        if ($this->needCounts["children"]) {
            $this->groupCounts = $this->insertGroupCountToNode($ids);
        }
    }

    protected function insertUserCountToNode(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        if ($ids === [-1]) {
            $sql = sprintf(
                "
            select u1.id, count(u1.id) as c from users u1, users u2 
            where u2.memberof @> ARRAY[u1.id] and u1.accounttype = 'G' and u2.accounttype='U' group by u1.id;
"
            );
        } else {
            $sql = sprintf(
                "
            select u1.id, count(u1.id) as c 
            from users u1, users u2 where u2.memberof @> ARRAY[u1.id] and u1.accounttype = 'G' and u2.accounttype='U' and u1.id in (%s) 
            group by u1.id
",
                implode(',', $ids)
            );
        }
        DbManager::query($sql, $accountCounts);


        $uc = [];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]] = intval($accountCount["c"]);
        }

        return $uc;
    }


    protected function insertDirectGroupCountToNode(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        if ($ids === [-1]) {
            $sql = sprintf(
                "
select u1.id, count(u2.id) as c
from groups, tv_groups u1, tv_groups u2
where groups.idgroup = u1.id and groups.iduser = u2.id and u1.accounttype= 'G' and u2.accounttype='G'
group by u1.id "
            );
        } else {
            $sql = sprintf(
                "
select u1.id, count(u2.id) as c
from groups, tv_groups u1, tv_groups u2
where groups.idgroup = u1.id and groups.iduser = u2.id and u1.accounttype= 'G' and u2.accounttype='G' and groups.idgroup in (%s)
group by u1.id ",
                implode(',', $ids)
            );
        }
        DbManager::query($sql, $accountCounts);


        $uc = [];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]] = intval($accountCount["c"]);
        }

        return $uc;
    }

    protected function insertGroupCountToNode(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        if ($ids === [-1]) {
            $sql = sprintf(
                "select g1.id, count(g1.id) as c from tv_groups g1, tv_groups g2 where g2.memberof @> ARRAY[g1.id] group by g1.id;"
            );
        } else {
            $sql = sprintf(
                "select g1.id, count(g1.id) as c from tv_groups g1, tv_groups g2 where g2.memberof @> ARRAY[g1.id] and g1.id in (%s) group by g1.id;",
                implode(',', $ids)
            );
        }
        DbManager::query($sql, $accountCounts);

        $uc = [];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]] = intval($accountCount["c"]);
        }

        return $uc;
    }

    protected function getEtag()
    {
        $sqlpart[] = "(select max(xmin::text::bigint) from users) as mu";
        $sqlpart[] = "(select max(xmin::text::bigint) from groups) as mg";
        $sqlpart[] = "(select string_agg(n_tup_ins::text,'-')" .
            " || string_agg(n_tup_upd::text,'-') " .
            "|| string_agg(n_tup_del::text,'-') || " .
            "string_agg(n_live_tup::text,'-') from pg_stat_user_tables where relname in ( 'users', 'groups') and schemaname = 'public') as st";

        $sql = "select " . implode(", ", $sqlpart);
        DbManager::query($sql, $stats);
        $tags = [
            \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "WVERSION"),
            $this->filterValue,
            json_encode($this->needCounts),
        ];
        foreach ($stats as $stat) {
            foreach ($stat as $info) {
                $tags[] = $info;
            }
        }
        return hash("md4", implode("-", $tags));
    }
}
