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
    protected $maxDepth = 0;
    protected $totalResults = 0;

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
        $pointerTree = [];

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


        // Get all group branches
        DbManager::query(
            "select groups.* from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G' order by uu.lastname, uu.login;",
            $groupBranches
        );


        // Compose the tree from branches
        foreach ($groupBranches as $branch) {
            $parent = intval($branch["idgroup"]);
            $child = intval($branch["iduser"]);

            $pointerTree[$parent]["content"][$child] =  &$pointerTree[$child];
        }

        // Delete no top branches into the tree
        foreach ($pointerTree as $accountId => $info) {
            if (!in_array($accountId, $topGroupIds)) {
                unset($pointerTree[$accountId]);
            }
        }


        // Walk by depth before to the tree
        $this->walkToTheTree($pointerTree);

        $this->insertUserCountToNode();

        return $response->withJson([
            "total" => $this->collected,
            "maxDepth" => $this->maxDepth,
            "data" => array_values($this->results)
        ]);
    }

    protected function insertUserCountToNode() {
        $ids=[];
        foreach ($this->results as $result) {
            $ids[$result["accountId"]]=$result["accountId"];
        }
        $sql=sprintf("select id, (select count(*) from users where memberof @> ARRAY[uu.id] and accounttype='U') as c from users uu where id in (%s)", implode(',',$ids));
        DbManager::query($sql, $accountCounts);

        $uc=[];
        foreach ($accountCounts as $accountCount) {
            $uc[$accountCount["id"]]=$accountCount["c"];
        }

        $sql=sprintf("select id, (select count(*) from users where memberof @> ARRAY[uu.id] and accounttype='G') as c from users uu where id in (%s)", implode(',',$ids));
        DbManager::query($sql, $accountCounts);

        $gc=[];
        foreach ($accountCounts as $accountCount) {
            $gc[$accountCount["id"]]=$accountCount["c"];
        }

        foreach ($this->results as &$result) {
            $result["userCount"]=$uc[$result["accountId"]];
            $result["subgroupCount"]=$gc[$result["accountId"]];
        }
    }

    protected function walkToTheTree(
        array $tree,
        $path = []
    ) {

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
            $this->maxDepth = max(count($path), $this->maxDepth);
            if ($this->depth === 0 || count($path) < $this->depth) {
                $this->collected++;
                if ($this->collected > $this->skip) {
                    if ($this->take === null || count($this->results) < $this->take) {
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
