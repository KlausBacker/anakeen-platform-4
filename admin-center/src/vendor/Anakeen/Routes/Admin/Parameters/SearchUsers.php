<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Router\ApiV2Response;

/**
 * Class SearchUsers
 *
 * @note Used by route : GET /api/v2/admin/parameters/users/search/{user}/
 * @package Anakeen\Routes\Admin\Parameters
 */
class SearchUsers
{
    protected $search;

    /**
     * Return the list of users containing the research terms in their first name, last name and/or login
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\Response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);

        $return = $this->searchUser($this->search);

        return ApiV2Response::withData($response, $return);
    }

    /**
     * Init parameters from request
     *
     * @param $args
     */
    private function initParameters($args)
    {
        $this->search = $args['user'];
    }

    /**
     * Get users containing the search term in their first name, last name and/or login
     *
     * @param $search
     * @return array
     */
    private function searchUser($search)
    {
        $searchTerm = preg_quote($search);
        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);
        $searchAccount->addFilter("login  ~* '%s' or lastname ~* '%s' or firstname ~* '%s' ", $searchTerm, $searchTerm, $searchTerm);

        $result = [];

        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $result[$currentAccount->id] = [
                "login"=> $currentAccount->login,
                "accountId" => $currentAccount->id,
                "firstname" => $currentAccount->firstname,
                "lastname" => $currentAccount->lastname
            ];
        }

        return array_values($result);
    }
}
