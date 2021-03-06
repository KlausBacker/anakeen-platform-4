<?php

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
        $password = $request->getParam("password");

        $currentUser = ContextManager::getCurrentUser();

        if (!$currentUser->checkpassword($password)) {
            $e = new Exception("IDENT0001");
            $e->setUserMessage(___("You entered an invalid password", "identityComponent"));

            throw $e;
        }

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $e = new Exception("IDENT0002");
            $e->setUserMessage(sprintf(___("Email address \"%s\" is not valid", "identityComponent"), $newEmail));

            throw $e;
        }

        $currentUser->mail = $newEmail;

        $err = $currentUser->modify();
        if ($err) {
            throw new Exception($err);
        }

        $data = [];
        $data["email"] = $currentUser->mail;
        return $response->withJson($data);
    }
}
