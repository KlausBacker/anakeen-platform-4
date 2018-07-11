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
        $return = $this->getFirstUsers();
        return ApiV2Response::withData($response, $return);
    }

    /**
     * Get 5 users form the database
     *
     * @return array
     */
    private function getFirstUsers()
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

        $firstUsers = array_slice(array_values($result), 0, 5);

        return $firstUsers;
    }
}
