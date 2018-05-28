<?php
/**
 * @author Anakeen
 */

namespace Anakeen\Components\Logout\Routes;

use Anakeen\Core\ContextManager;

/**
 * Class Logout
 * Logout with user's informations
 * @note Used by route : DELETE /components/logout/logout
 * @package Anakeen\Components\Logout\Routes
 */
class Logout
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $currentUser = ContextManager::getCurrentUser();

        $coreLogout = new \Anakeen\Routes\Authent\Logout();
        $response = $coreLogout($request, $response, []);

        $jsonData = json_decode((string) $response->getBody(), true);
        $jsonData["login"] = $currentUser->login;
        $jsonData["firstName"] = $currentUser->firstname;
        $jsonData["lastName"] = $currentUser->lastname;

        return $response->withJson($jsonData);
    }
}