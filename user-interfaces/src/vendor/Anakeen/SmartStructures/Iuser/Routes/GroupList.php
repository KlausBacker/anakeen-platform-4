<?php

namespace Anakeen\SmartStructures\Iuser\Routes;

use Anakeen\Accounts\SearchAccounts;
use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
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
        $take= $request->getQueryParam("take");
        $sort = $request->getQueryParam("sort");

        $accountId=$args["accountid"];

        $smartAccount=SmartElementManager::getDocument($accountId);
        if (!$smartAccount) {
             throw new Exception(sprintf("Account #%d not found", $args["accountid"]));
        }
        /** @var Iuser $smartAccount */
        $account=$smartAccount->getAccount();
        if (!$account || !$account->isAffected()) {
            throw new Exception(sprintf("System account #%d not found", $args["accountid"]));
        }

        $g=new \Group("", $account->id);
        $parentGroups=[];
        if ($g->getGroups()) {
            $parentGroups = $g->groups;
        }
        $searchAccount = new \Anakeen\Accounts\SearchAccounts();
        $searchAccount->setTypeFilter(\Anakeen\Accounts\SearchAccounts::groupType);
        if ($not) {

            if ($parentGroups) {
                $searchAccount->addFilter(sprintf("id not in (%s)", implode(",", $parentGroups)));
            }
        } else {
            if ($parentGroups) {
                $searchAccount->addFilter(sprintf("id in (%s)", implode(",", $parentGroups)));
            } else {
                $searchAccount->addFilter("false");
            }
        }
        if ($sort) {
            $sortString = "";
            foreach ($sort as $currentSort) {
                $sortString .= $currentSort["field"]." ".$currentSort["dir"]." ";
            }
            $searchAccount->setOrder($sortString);
        }

        if ($filter) {
            foreach ($filter["filters"] as $currentFilter) {
                if ($currentFilter["field"] === "group") {
                    $searchAccount->addGroupFilter($currentFilter["value"]);
                } else {
                    $searchAccount->addFilter($currentFilter["field"]." ~* '%s'", preg_quote($currentFilter["value"]));
                }
            }
        }

        //count max result
        $request = $searchAccount->getQuery();
        DbManager::query("select count(*) from (".$request.") as nbResult;", $nResult, true, true);

        if ($skip !== null) {
            $searchAccount->setStart($skip);
        }
        if ($take !== null) {
            $searchAccount->setSlice($take);
        }


        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $result[$currentAccount->id] = [
                "login"=> $currentAccount->login,
                "id" => $currentAccount->fid,
                "accountId" => $currentAccount->id,
                "title" => \DocTitle::getTitle($currentAccount->fid),
                "mail"=> $currentAccount->mail,
                "firstname" => $currentAccount->firstname,
                "lastname" => $currentAccount->lastname
            ];
        }

        return $response->withJson([
            "total"=> $nResult,
            "data"=> array_values($result)
        ]);
    }
}
