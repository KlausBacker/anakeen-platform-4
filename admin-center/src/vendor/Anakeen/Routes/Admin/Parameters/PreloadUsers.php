<?php

namespace Anakeen\Routes\Admin\Parameters;


class PreloadUsers
{
    /**
     * Return the first 5 users of the database to fill the grid in parameters plugin
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $result = [];

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);

        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $result[$currentAccount->id] = [
                "login" => $currentAccount->login,
                "accountId" => $currentAccount->id,
                "firstname" => $currentAccount->firstname,
                "lastname" => $currentAccount->lastname
            ];
        }

        $firstUsers = array_slice(array_values($result), 0, 5);

        return $response->withJson($firstUsers);
    }
}