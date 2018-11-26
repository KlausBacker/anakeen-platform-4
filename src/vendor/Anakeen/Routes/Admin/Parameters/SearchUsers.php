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
    protected $page;
    protected $take;

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
        $this->initParameters($request, $args);

        $return = $this->searchUser($this->search, $this->page, $this->take);

        return ApiV2Response::withData($response, $return);
    }

    /**
     * Init parameters from request
     *
     * @param \Slim\Http\request $request
     * @param $args
     */
    private function initParameters(\Slim\Http\request $request, $args)
    {
        $this->page = $request->getQueryParam("page");
        $this->take = $request->getQueryParam("take");
        $this->search = $args['user'];
    }

    /**
     * Get users containing the search term in their first name, last name and/or login
     *
     * @param $search
     * @param $page
     * @param $take
     * @return array
     */
    private function searchUser($search, $page, $take)
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

        $return['users'] = array_slice(array_values($result), ($page - 1) * $take, $take);
        $return['total'] = sizeof($result);
        return $return;
    }
}
