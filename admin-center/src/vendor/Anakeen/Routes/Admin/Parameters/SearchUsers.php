<?php

namespace Anakeen\Routes\Admin\Parameters;


class SearchUsers
{
    /**
     * Return the list of users containing the research terms in their first name, last name and/or login
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
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