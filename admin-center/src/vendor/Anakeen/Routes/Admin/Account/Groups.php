<?php

namespace Anakeen\Routes\Admin\Account;


use Anakeen\Core\DbManager;

class Groups
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args) {

        $filter = $request->getQueryParam("filter");

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::groupType);
        if ($filter !== null) {
            $searchAccount->addFilter("lastname ~* '%s' OR login ~* '%s'", preg_quote($filter));
        }

        $groups = [];
        //First iteration
        foreach ($searchAccount->search() as $currentAccount) {
            $nbUser = 0;
            $userList = $currentAccount->getAllMembers();
            if (is_array($userList)) {
                $nbUser = count($userList);
            }
            /* @var $currentAccount \Anakeen\Core\Account */
            $groups[$currentAccount->id] = [
                "login"=> $currentAccount->login,
                "documentId" => $currentAccount->fid,
                "accountId" => $currentAccount->id,
                "parents" => $currentAccount->getGroupsId(),
                "title" => getDocTitle($currentAccount->fid),
                "nbUser" => $nbUser,
                "items" => []
            ];
        }

        //Compute nb total of users
        $nbUsers = 0;
        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);
        $request = $searchAccount->getQuery();
        DbManager::query("select count(*) from (".$request.") as nbResult;", $nbUsers, true, true);

        return $response->withJson(["groups"=> $groups, "nbUsers"=> $nbUsers]);

    }
}