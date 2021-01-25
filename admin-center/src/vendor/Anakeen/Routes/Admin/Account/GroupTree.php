<?php

namespace Anakeen\Routes\Admin\Account;

use Anakeen\Core\DbManager;
use Anakeen\Router\ApiV2Response;

/**
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
    protected $prefix;
    /**
     * @var array
     */
    protected $uc = [];
    protected $gc = [];
    protected $needCounts = true;
    protected $kk = 0;
    protected $dgc;
    protected $filterMatchOnly = false;

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->filterValue = $request->getQueryParam("filter");
        $this->filterMatchOnly = $request->getQueryParam("filterMatchOnly") === "true";
        $this->needCounts = json_decode($request->getQueryParam("getCounts"), true);

        $groupId = $args["groupid"] ?? 0;
        if ($groupId === "all") {
            $this->expandAll = true;
        } else {
            $this->parentId = $groupId;
        }


        $etag = $this->getEtag();
        if ($etag) {
            $response = ApiV2Response::withEtag($request, $response, $etag);
            if (ApiV2Response::matchEtag($request, $etag)) {
                return $response;
            }
        }


        $this->prefix = uniqid("i");

        if ($this->expandAll === true) {
            $data = $this->getTreeAllData();
        } else {
            $data = $this->getTreeData();
        }

        $response = ApiV2Response::withData($response, $data);
        if (empty($data["error"]) && $etag) {
            $response = ApiV2Response::withEtag($request, $response, $etag);
        }
        return $response;
    }

    /**
     * Recompose array to delete pointer link
     * Add items and children counts if needed
     * @param array $values
     * @return array
     */
    protected function childrenValue(array $values)
    {
        $nv = [];
        foreach ($values as $k => $v) {
            if (!empty($v["children"])) {
                $v["loadedChildrenCount"] = count($v["children"]);
                if ($this->expandAll === false) {
                    unset($v["children"]);
                } else {
                    $v["children"] = $this->childrenValue($v["children"]);
                }
            } else {
                $v["loadedChildrenCount"] = 0;
            }
            $v["directChildsCount"] = $this->dgc[$v["id"]] ?? null;
            $v["index"] = $this->prefix . ($this->kk++);
            if ($this->needCounts["item"] === true) {
                $v["itemCount"] = $this->uc[$v["id"]] ?? null;
            }
            if ($this->needCounts["children"] === true) {
                $v["childrenCount"] = $this->gc[$v["id"]] ?? null;
            }

            $nv[] = $v;
        }

        return $nv;
    }

    protected function getTreeData()
    {

        // Get top groups

        $mi = microtime(true);


        $m0 = microtime(true);

        $pointerTree = [];


        if ($this->parentId === 0) {
            DbManager::query(
                "select id, login, fid as accountid, lastname as name " .
                " from users where accounttype='G' and id not in " .
                "(select iduser from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G') order by lastname, id;",
                $allGroupInfo
            );
        } else {
            DbManager::query(
                sprintf(
                    "select users.id, users.login, users.fid as accountid, users.lastname as name from users, groups" .
                    " where idgroup = %d and groups.iduser = users.id and users.accounttype='G' order by lastname, id;",
                    $this->parentId
                ),
                $allGroupInfo
            );
        }

        if ($this->filterValue) {
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
                        "select users.id, users.login, users.fid as accountid, users.lastname as name from users where accounttype='G' and %s;",
                        implode(" and ", $wheres)
                    ),
                    $matchesGroup
                );
            }


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


        $m1 = microtime(true);

        foreach ($allGroupInfo as $groupInfo) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $id = intval($groupInfo["id"]);
            $result[$id] = $groupInfo;
            $pointerTree[$id] = &$result[$id];
        }


        $m2 = microtime(true);

        // Get all group branches
        DbManager::query(
            "select groups.* from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G' order by uu.lastname, uu.login;",
            $groupBranches
        );


        $m3 = microtime(true);
        // Compose the tree from branches
        foreach ($groupBranches as $branch) {
            $parent = intval($branch["idgroup"]);
            $child = intval($branch["iduser"]);

            if (isset($pointerTree[$parent])) {
                $pointerTree[$parent]["children"][$child] = true;
            }
        }


        $m4 = microtime(true);


        $ids = array_keys($pointerTree);

        $this->dgc = $this->insertDirectGroupCountToNode($ids);
        if ($this->needCounts["item"]) {
            $this->uc = $this->insertUserCountToNode($ids);
        }
        if ($this->needCounts["children"]) {
            $this->gc = $this->insertGroupCountToNode($ids);
        }

        $m5 = microtime(true);
        $this->results = $this->childrenValue($pointerTree);


        $m6 = microtime(true);

        // Walk by depth before to the tree
        //  $this->walkToTheTree($pointerTree);


        //$pointerTree = array_slice($pointerTree, 0, 3000);
        $m7 = microtime(true);

        return [
            "debug" => [
                "m0" => sprintf("%dms", ($m0 - $mi) * 1000),
                "m1" => sprintf("%dms", ($m1 - $m0) * 1000),
                "m2" => sprintf("%dms", ($m2 - $m1) * 1000),
                "m3" => sprintf("%dms", ($m3 - $m2) * 1000),
                "m4" => sprintf("%dms", ($m4 - $m3) * 1000),
                "m5" => sprintf("%dms", ($m5 - $m4) * 1000),
                "m6" => sprintf("%dms", ($m6 - $m5) * 1000),
                "all" => sprintf("%.dms", ($m7 - $mi) * 1000)
            ],
            "treeData" => $this->results
        ];
    }


    protected function getTreeAllData()
    {

        // Get top groups

        $mi = microtime(true);
        DbManager::query(
            "select id " .
            " from users where accounttype='G' and id not in " .
            "(select iduser from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G');",
            $topGroupIds,
            true
        );


        $m0 = microtime(true);

        $pointerTree = [];


        if ($this->filterValue) {
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
                        "select users.id, users.login, users.fid as accountid, users.lastname as name from users where accounttype='G' and %s;",
                        implode(" and ", $wheres)
                    ),
                    $matchesGroup
                );
            }

            if (count($matchesGroup) > 0) {
                $matchesGroupIds = [];
                foreach ($matchesGroup as &$match) {
                    $matchesGroupIds[$match["id"]] = $match["id"];
                    $match["match"] = true;
                }


                $sql = sprintf(
                    "with recursive agroups(gid) as (
 select idgroup from groups,users where iduser in (%s) and users.id=groups.idgroup
union
 select idgroup from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.idgroup
) select users.id, users.login, users.fid as accountid, users.lastname as name from agroups, users where users.id=agroups.gid and users.accounttype='G' order by lastname",
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
                "select id, login, fid as accountid, lastname as name from users where accounttype='G' order by lastname;",
                $allGroupInfo
            );
        }


        if (count($allGroupInfo) > 0) {
            $m1 = microtime(true);


            foreach ($allGroupInfo as $groupInfo) {
                /* @var $currentAccount \Anakeen\Core\Account */
                $id = intval($groupInfo["id"]);
                $result[$id] = $groupInfo;
                $pointerTree[$id] = &$result[$id];
            }


            $m2 = microtime(true);

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
                DbManager::query(
                    "select groups.* from users gu, users uu, groups " .
                    "where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G' order by uu.lastname, uu.login;",
                    $groupBranches
                );
            }


            $m3 = microtime(true);
            // Compose the tree from branches
            foreach ($groupBranches as $branch) {
                $parent = intval($branch["idgroup"]);
                $child = intval($branch["iduser"]);

                if (isset($pointerTree[$parent]) && isset($pointerTree[$child])) {
                    $pointerTree[$parent]["children"][$child] =  &$pointerTree[$child];
                }
            }


            $m4 = microtime(true);


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

            $m5 = microtime(true);

            if ($this->needCounts["item"]) {
                if ($this->filterValue) {
                    $this->uc = $this->insertUserCountToNode($groupIds);
                } else {
                    $this->uc = $this->insertUserCountToNode([-1]);
                }
            }
            if ($this->needCounts["children"]) {
                if ($this->filterValue) {
                    $this->gc = $this->insertGroupCountToNode($groupIds);
                } else {
                    $this->gc = $this->insertGroupCountToNode([-1]);
                }
            }
            $this->dgc = $this->insertDirectGroupCountToNode([-1]);

            $m6 = microtime(true);
            $this->results = $this->childrenValue($pointerTree);


            // Walk by depth before to the tree
            //  $this->walkToTheTree($pointerTree);


            //$pointerTree = array_slice($pointerTree, 0, 3000);
            $m7 = microtime(true);
            return [
                "debug" => [
                    "m0" => sprintf("%dms", ($m0 - $mi) * 1000),
                    "m1" => sprintf("%dms", ($m1 - $m0) * 1000),
                    "m2" => sprintf("%dms", ($m2 - $m1) * 1000),
                    "m3" => sprintf("%dms", ($m3 - $m2) * 1000),
                    "m4" => sprintf("%dms", ($m4 - $m3) * 1000),
                    "m5" => sprintf("%dms", ($m5 - $m4) * 1000),
                    "m6" => sprintf("%dms", ($m6 - $m5) * 1000),
                    "all" => sprintf("%.dms", ($m7 - $mi) * 1000)
                ],
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

    protected function insertUserCountToNode(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        if ($ids === [-1]) {
            $sql = sprintf(
                "select id, (select count(*) from users where memberof @> ARRAY[uu.id] and accounttype='U') as c from users uu where uu.accounttype='G'"
            );
        } else {
            $sql = sprintf(
                "select id, (select count(*) from users where memberof @> ARRAY[uu.id] and accounttype='U') as c from users uu where uu.accounttype='G' and id in (%s)",
                implode(',', $ids)
            );
        }
        DbManager::query($sql, $accountCounts);


        $uc = [];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]] = intval($accountCount["c"]);
        }


        // create extension if not exists intarray;
        // create index uv_idx on users using gin(memberof gin__int_ops);

        return $uc;
    }


    protected function insertDirectGroupCountToNode(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        if ($ids === [-1]) {
            $sql = sprintf(
                "select id, (select count(*) from groups, users where  idgroup = uu.id and iduser=users.id and users.accounttype='G') as c from users uu where uu.accounttype='G'"
            );
        } else {
            $sql = sprintf(
                "select id, " .
                "(select count(*) from groups, users " .
                "where  idgroup = uu.id and iduser=users.id and users.accounttype='G') as c from users uu where uu.accounttype='G' and id in (%s)",
                implode(',', $ids)
            );
        }
        DbManager::query($sql, $accountCounts);


        $uc = [];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]] = intval($accountCount["c"]);
        }


        // create extension if not exists intarray;
        // create index uv_idx on users using gin(memberof gin__int_ops);

        return $uc;
    }

    protected function insertGroupCountToNode(array $ids)
    {
        if (count($ids) === 0) {
            return [];
        }

        if ($ids === [-1]) {
            $sql = sprintf(
                "select id, (select count(*) from users where memberof @> ARRAY[uu.id] and accounttype='G') as c from users uu where uu.accounttype='G'"
            );
        } else {
            $sql = sprintf(
                "select id, (select count(*) from users where memberof @> ARRAY[uu.id] and accounttype='G') as c from users uu where uu.accounttype='G' and id in (%s)",
                implode(',', $ids)
            );
        }
        DbManager::query($sql, $accountCounts);


        $uc = [];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]] = intval($accountCount["c"]);
        }


        // create extension if not exists intarray;
        // create index uv_idx on users using gin(memberof gin__int_ops);

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
            $this->filterValue
        ];
        foreach ($stats as $stat) {
            foreach ($stat as $info) {
                $tags[] = $info;
            }
        }
        return implode("-", $tags);
    }
}
