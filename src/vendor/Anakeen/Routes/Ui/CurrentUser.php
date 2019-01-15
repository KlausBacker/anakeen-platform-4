<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Core\ContextManager;
use Anakeen\Router\ApiV2Response;

/**
 * Class CurrentUser
 * Fetch user information from server
 * @note Used by route : GET /api/v2/ui/users/current
 * @package Anakeen\Components\Identity\Routes
 */
class CurrentUser
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $currentUser = ContextManager::getCurrentUser();

        $data = [];
        $data["login"] = $currentUser->login;
        $initials = substr($currentUser->firstname, 0, 1).substr($currentUser->lastname, 0, 1);
        $data["initials"] = $initials;
        $data["firstName"] = $currentUser->firstname;
        $data["lastName"] = $currentUser->lastname;
        $data["email"] = $currentUser->mail;

        $locale = ContextManager::getLanguage();
        $data["locale"] = $locale;

        return ApiV2Response::withData($response, $data);
    }
}
