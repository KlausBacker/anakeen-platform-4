<?php
/**
 * Created by PhpStorm.
 * User: charles
 * Date: 18/05/18
 * Time: 15:05
 */

namespace Anakeen\Routes\Admin\Account;


use Dcp\Sacc\Exception;

class Users
{
    private static $fields = ["login", "lastname", "firstname", "mail"];

    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response) {
        $result = [];
        $filter = $request->getQueryParam("filter");
        $skip = $request->getQueryParam("skip");
        $take= $request->getQueryParam("take");

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);
        if (isset($filter)) {
            foreach ($filter["filters"] as $currentFilter) {
                if ($currentFilter["field"] === "group") {
                    $searchAccount->addGroupFilter($currentFilter["value"]);
                }
            }
        }

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
            "total"=> count($result),
            "data"=> array_values($result),
            "filter"=>$filter
        ]);

    }
}