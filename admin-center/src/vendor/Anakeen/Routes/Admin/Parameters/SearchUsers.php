<?php

namespace Anakeen\Routes\Admin\Parameters;


class SearchUsers
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $search = preg_quote($args['user']);
        $result = [];

        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);
        $searchAccount->addFilter("login  ~* '%s' or lastname ~* '%s' or firstname ~* '%s' ", $search, $search, $search);


        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $result[$currentAccount->id] = [
                "login"=> $currentAccount->login,
                "accountId" => $currentAccount->id,
                "firstname" => $currentAccount->firstname,
                "lastname" => $currentAccount->lastname
            ];
        }

        return $response->withJson(array_values($result));
    }
}