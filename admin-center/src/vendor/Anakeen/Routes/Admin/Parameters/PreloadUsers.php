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
    const PAGESIZE = 50;
    private $take = self::PAGESIZE;
    private $skip = 0;

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
        $return = $this->getUsers($this->skip, $this->take);
        return ApiV2Response::withData($response, $return);
    }

    /**
     * Init parameters from request
     *
     * @param \Slim\Http\request $request
     */
    private function initParameters(\Slim\Http\request $request)
    {
        $this->take = intval($request->getQueryParam("take", self::PAGESIZE));
        $this->skip = intval($request->getQueryParam("skip", 0));

    }

    /**
     * Get requested users from database
     *
     * @param $skip
     * @param $take
     * @return array
     */
    private function getUsers($skip, $take)
    {
        $searchAccount = new \Anakeen\Accounts\SearchAccounts();
        $searchAccount->setTypeFilter(\Anakeen\Accounts\SearchAccounts::userType);

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
        $return["users"] = array_slice(array_values($result), $skip, $take);
        $return["total"] = sizeof($result);
        $return["take"] = $take;
        $return["skip"] = $skip;

        return $return;
    }
}
