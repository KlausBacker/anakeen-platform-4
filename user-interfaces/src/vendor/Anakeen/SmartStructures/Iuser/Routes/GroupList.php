<?php

namespace Anakeen\SmartStructures\Iuser\Routes;

use Anakeen\Core\DbManager;
use Anakeen\Core\Utils\Postgres;
use Anakeen\Exception;
use Anakeen\SmartElementManager;
use SmartStructure\Iuser;

/**
 * @note use by route GET /api/v2/ui/account/groups/{accountid}
 */
class GroupList
{

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

        $not = $request->getQueryParam("not");
        $filter = $request->getQueryParam("filter");
        $skip = $request->getQueryParam("skip");
        $take = $request->getQueryParam("take");
        $addedgroups = $request->getQueryParam("addedGroups");
        $deletedgroups = $request->getQueryParam("deletedGroups");

        $accountId = $args["accountid"];

        $smartAccount = SmartElementManager::getDocument($accountId);
        if (!$smartAccount) {
            throw new Exception(sprintf("Account #%d not found", $args["accountid"]));
        }
        /** @var Iuser $smartAccount */
        $account = $smartAccount->getAccount();
        if (!$account || !$account->isAffected()) {
            throw new Exception(sprintf("System account #%d not found", $args["accountid"]));
        }

        $g = new \Group("", $account->id);
        $parentGroups = [];
        if ($g->getGroups()) {
            $parentGroups = $g->groups;
        }
        $searchAccount = new \Anakeen\Accounts\SearchAccounts();
        $searchAccount->setTypeFilter(\Anakeen\Accounts\SearchAccounts::groupType);

        $groupIds = [];
        $toaddIds = [];
        $todeleteIds = [];
        if ($parentGroups) {
            foreach ($parentGroups as $parentGroup) {
                $gid = intval($parentGroup);
                $groupIds[$gid] = $gid;
            }
        }
        if ($deletedgroups) {
            foreach ($deletedgroups as $deletedgroup) {
                $gid = intval($deletedgroup);
                if (isset($groupIds[$gid])) {
                    unset($groupIds[$gid]);
                    $todeleteIds[$gid] = true;
                }
            }
        }

        if ($addedgroups) {
            foreach ($addedgroups as $parentGroup) {
                $gid = intval($parentGroup);
                if (!isset($groupIds[$gid])) {
                    $groupIds[$gid] = $gid;
                    $toaddIds[$gid] = true;
                }
            }
        }

        if ($not) {
            if ($groupIds) {
                $searchAccount->addFilter(sprintf("id not in (%s)", implode(",", $groupIds)));
            }
        } elseif ($groupIds) {
            $searchAccount->addFilter(sprintf("id in (%s)", implode(",", $groupIds)));
        } else {
            $searchAccount->addFilter("false");
        }


        $searchAccount->setOrder("lastname, login");


        if ($filter) {
            $searchAccount->addFilter("lastname ~* '%s'", preg_quote($filter));
        }

        //count max result
        $request = $searchAccount->getQuery();
        DbManager::query("select count(*) from (" . $request . ") as nbResult;", $nResult, true, true);

        if ($skip !== null) {
            $searchAccount->setStart($skip);
        }
        if ($take !== null) {
            $searchAccount->setSlice($take);
        }


        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $uid = intval($currentAccount->id);
            $result[$uid] = [
                "login" => $currentAccount->login,
                "id" => $currentAccount->fid,
                "accountId" => $uid,
                "title" => \DocTitle::getTitle($currentAccount->fid),
                "firstname" => $currentAccount->firstname,
                "lastname" => $currentAccount->lastname,
                "tag" => isset($todeleteIds[$uid]) ? "todelete" : (isset($toaddIds[$uid]) ? "toadd" : ""),
                "pathes" => []
            ];
        }

        if ($result) {
            $pathes = $this->getAccountPathes(array_keys($result));
            foreach ($pathes as $uid => $paths) {
                $result[$uid]["pathes"] = $paths;
            }
        }


        return $response->withJson([
            "total" => $nResult,
            "data" => array_values($result),
            "pagerTranslation" => ___("{0} - {1} of {2} results", "smart-grid")
        ]);
    }

    /**
     * Request to get all group path for some groups
     * @param int[] $ids group ids
     * @return array
     * @throws \Anakeen\Database\Exception
     */
    protected function getAccountPathes($ids)
    {
        $sql = sprintf(
            "
with recursive agroups(gid, logins, aid) as (
 select idgroup,  ARRAY[]::text[] as c, iduser as aid  from groups,users where iduser in (%s) and users.id=groups.idgroup
union
 select idgroup,  users.lastname ||  agroups.logins as c, agroups.aid from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.iduser
) select users.lastname || agroups.logins as path, agroups.aid from agroups, users where users.id=agroups.gid and users.accounttype='G' and users.id in (%s);
",
            implode(",", $ids),
            implode(",", $this->getTopGroupIds())
        );
        DbManager::query($sql, $paths);
        $accountPathes = [];
        foreach ($paths as $path) {
            $accountPathes[intval($path["aid"])][] = Postgres::stringToArray($path["path"]);
        }
        return $accountPathes;
    }

    protected function getTopGroupIds()
    {
        DbManager::query(
            "select id " .
            " from users where accounttype='G' and id not in " .
            "(select iduser from users gu, users uu, groups where idgroup=gu.id and iduser=uu.id and uu.accounttype='G' and gu.accounttype='G');",
            $topGroupIds,
            true
        );
        return $topGroupIds;
    }
}
