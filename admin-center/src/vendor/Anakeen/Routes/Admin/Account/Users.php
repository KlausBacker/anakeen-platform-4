<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 18/05/18
 * Time: 15:05
 */

namespace Anakeen\Routes\Admin\Account;


use Anakeen\Core\DbManager;
use Dcp\Sacc\Exception;

class Users
{

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @return \Slim\Http\Response
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response) {
        $result = [];
        $filter = $request->getQueryParam("filter");
        $skip = $request->getQueryParam("skip");
        $take= $request->getQueryParam("take");
        $sort = $request->getQueryParam("sort");

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);
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
        if($take !== null) {
            $searchAccount->setSlice($take);
        }

        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $result[$currentAccount->id] = [
                "login"=> $currentAccount->login,
                "id" => $currentAccount->fid,
                "accountId" => $currentAccount->id,
                "title" => getDocTitle($currentAccount->fid),
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