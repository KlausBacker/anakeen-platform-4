<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Router\ApiV2Response;

/**
 * Class PreloadUsers
 *
 * @note Used by route : GET /api/v2/admin/parameters/users/
 * @package Anakeen\Routes\Admin\Parameters
 */
class PreloadUsers
{
    protected $page;
    protected $take;

    /**
     * Return 5 users from the server, to pre-load a list of users
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($request);
        $return = $this->getUsers($this->page, $this->take);
        return ApiV2Response::withData($response, $return);
    }

    /**
     * Init parameters from request
     *
     * @param \Slim\Http\request $request
     */
    private function initParameters(\Slim\Http\request $request)
    {
        $this->page = $request->getQueryParam("page");
        $this->take = $request->getQueryParam("take");
    }

    /**
     * Get requested users from database
     *
     * @param $page
     * @param $take
     * @return array
     */
    private function getUsers($page, $take)
    {
        $searchAccount = new \SearchAccount();
        $searchAccount->setTypeFilter(\SearchAccount::userType);

        $result = [];

        foreach ($searchAccount->search() as $currentAccount) {
            /* @var $currentAccount \Anakeen\Core\Account */
            $result[$currentAccount->id] = [
                "login" => $currentAccount->login,
                "accountId" => $currentAccount->id,
                "firstname" => $currentAccount->firstname,
                "lastname" => $currentAccount->lastname
            ];
        }

        $return["users"] = array_slice(array_values($result), ($page - 1) * $take, $take);
        $return["total"] = sizeof($result);
        $return["take"] = $take;
        $return["page"] = $page;

        return $return;
    }
}
