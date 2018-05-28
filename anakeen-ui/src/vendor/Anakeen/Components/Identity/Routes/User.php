<?php
/**
 * @author anakeen
 */

namespace Anakeen\Components\Identity\Routes;

use Anakeen\Core\ContextManager;

/**
 * Class User
 * Fetch user information from server
 * @note Used by route : GET /components/identity/user
 * @package Anakeen\Components\Identity\Routes
 */
class User
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

        return $response->withJson($data);
    }
}