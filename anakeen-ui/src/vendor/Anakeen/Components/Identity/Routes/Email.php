<?php /** @noinspection ALL */

/**
 * @author Anakeen
 */

namespace Anakeen\Components\Identity\Routes;


use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;

/**
 * Class Email
 * Modify User email
 * @note Used by route : PUT /components/identity/email
 * @package Anakeen\Components\Identity\Routes
 */
class Email
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $newEmail = $request->getParam("email");

        $currentUser = ContextManager::getCurrentUser();
        $currentUser->mail = $newEmail;

        $err= $currentUser->modify();
        if ($err) {
            throw new Exception($err);
        }

        $data = [];
        $data["email"] = $currentUser->mail;
        return $response->withJson($data);
    }
}