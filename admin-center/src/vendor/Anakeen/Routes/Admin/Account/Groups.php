<?php

namespace Anakeen\Routes\Admin\Account;

use Anakeen\Core\DbManager;

/**
 * @note use by route GET /api/v2/admin/account/groups/
 */
class Groups
{
    /**
     * @var array
     */
    protected $results = [];
    protected $skip = 0;
    protected $take;
    protected $collected = 0;
    protected $depth;
    protected $maxDepth=0;

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     * @throws \Anakeen\Database\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $result = [];
        $filter = $request->getQueryParam("filter");
        $this->skip = intval($request->getQueryParam("skip"));
        $this->take = $request->getQueryParam("take");
        $this->depth = $request->getQueryParam("depth", 0);
        $sort = $request->getQueryParam("sort");

        // Get top groups

        DbManager::query(
            "select id " .
            " from users where accounttype='G' and id not in " .
            "(select iduser from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G');",
            $topGroupIds,
            true
        );
        $searchAccount = new \Anakeen\Accounts\SearchAccounts();
        $searchAccount->setTypeFilter(\Anakeen\Accounts\SearchAccounts::groupType);
        if ($sort) {
            $sortString = "";
            foreach ($sort as $currentSort) {
                $sortString .= $currentSort["field"] . " " . $currentSort["dir"] . " ";
            }
            $searchAccount->setOrder($sortString);
        }

        if ($filter) {
            foreach ($filter["filters"] as $currentFilter) {
                if ($currentFilter["field"] === "group") {
                    $searchAccount->addGroupFilter($currentFilter["value"]);
                } else {
                    $searchAccount->addFilter(
                        $currentFilter["field"] . " ~* '%s'",
                        preg_quote($currentFilter["value"])
                    );
                }
            }
        }


        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $id = intval($currentAccount->id);
            $result[$id] = [
                "info" => [
                    "login" => $currentAccount->login,
                    "id" => $currentAccount->fid,
                    "accountId" => $id,
                    "lastname" => $currentAccount->lastname
                ]
            ];

            $pointerTree[$id] = &$result[$id];
        }


        DbManager::query(
            "select groups.* from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G' order by uu.lastname, uu.login;",
            $groupBranches
        );

        $tree = [];
        $childIds = [];

        // $pointerTree = [];
        foreach ($groupBranches as $branch) {
            $parent = intval($branch["idgroup"]);
            $child = intval($branch["iduser"]);

            $pointerTree[$parent]["content"][$child] =  &$pointerTree[$child];
            // $pointerTree[$child["id"]] = &$pointerTree[$member]["content"][$child["id"]];
        }
        // print_r($pointerTree);

        foreach ($pointerTree as $accountId => $info) {
            if (!in_array($accountId, $topGroupIds)) {
                unset($pointerTree[$accountId]);
            }
        }

        $nResult = count($groupBranches)+ count($topGroupIds);

        // Walk by depth before to the tree
        $this->walkToTheTree($pointerTree);


        return $response->withJson([
            "total" => $nResult,
            "maxDepth" => $this->maxDepth,
            "data" => array_values($this->results)
        ]);
    }

    protected function getChildGroups(
        array $gids
    ) {
        $sql = sprintf(
            "select users.id, fid, memberof, lastname, login from users, groups where accounttype='G' and groups.iduser=users.id and groups.idgroup in (%s) limit 100",
            implode(", ", $gids)
        );
        print $sql;
        DbManager::query($sql, $childs);
        return $childs;
    }

    protected function walkToTheTree(
        array $tree,
        $path = []
    ) {
        if ($this->skip !== null) {
            //  $searchAccount->setStart($skip);
        }
        if ($this->take !== null) {
            if (count($this->results) > $this->take) {
                return;
            }
        }
        foreach ($tree as $groupAccount) {
            if (empty($groupAccount["info"])) {
                continue;
            }
            $info = $groupAccount["info"];
            $info["path"] = $path;
            $this->collected++;
            $this->maxDepth=max(count($path), $this->maxDepth);
            if ($this->collected > $this->skip) {
                if ($this->take !== null && count($this->results) < $this->take) {
                    if ($this->depth === 0 || count($path) < $this->depth) {
                        $this->results[] = $info;
                    }
                }
            }
            if (!empty($groupAccount["content"])) {
                $this->walkToTheTree($groupAccount["content"], array_merge($path, [$info["lastname"]]));
            }
        }
    }
}
