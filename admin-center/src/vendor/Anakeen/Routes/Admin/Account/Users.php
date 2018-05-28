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
    /**
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $responsz
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response) {
        $result = [];
        $filter = $request->getQueryParam("filter");
        $group = $request->getQueryParam("group");

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);
        if ($group !== null) {
            $searchAccount->addGroupFilter($group);
        }
        if ($filter !== null) {
            $searchAccount->addFilter("concat_ws(' ', lastname, firstname) ~* '%s' OR login ~* '%s'", preg_quote($filter), preg_quote($filter));
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

        return $response->withJson($result);

    }
}