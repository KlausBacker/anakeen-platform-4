<?php

namespace Anakeen\Routes\Admin\Parameters;

use Anakeen\Core\Account;
use Anakeen\Core\AccountManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class GetUserFromSeId
 *
 * @note : Used by route : GET /api/v2/admin/parameters/smartform/{seUserId}
 * @package Anakeen\Routes\Admin\Parameters
 */
class GetUserFromSeId
{
    protected $userLogin;
    protected $user;

    /**
     * Get the user id, login and display value
     *
     * @param \Slim\Http\request $request
     * @param \Slim\Http\response $response
     * @param $args
     * @return \Slim\Http\response
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $this->initParameters($args);
        $userElements = $this->getUserElement();
        return ApiV2Response::withData($response, $userElements);
    }

    /**
     * Get account user
     *
     * @param $args
     */
    private function initParameters($args)
    {
        $userId = AccountManager::getIdFromSEId(intval($args["seUserId"]));
        $this->user = new Account("", $userId);
    }

    /**
     * Format login, user id en display value to return
     */
    private function getUserElement()
    {
        $element = [
            "login" => $this->user->login,
            "userId" => $this->user->id,
            "displayValue" => $this->user->getAccountName()
        ];
        return $element;
    }
}
