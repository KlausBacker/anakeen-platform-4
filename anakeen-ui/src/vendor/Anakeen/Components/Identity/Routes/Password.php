<?php

namespace Anakeen\Components\Identity\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Router\Exception;

/**
 * Class Password
 * Verify old password and change User password
 * @note Used by route : PUT /components/identity/password
 * @package Anakeen\Components\Identity\Routes
 */
class Password
{
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $oldPassword = $request->getParam("oldPassword");
        $newPassword = $request->getParam("newPassword");

        $currentUser = ContextManager::getCurrentUser();

        if (!$currentUser->checkpassword($oldPassword)) {
            $e = new Exception("IDENT0201");
            $e->setUserMessage(___("Wrong password", "identityComponent"));

            throw $e;
        }

        $currentUser->password_new = $newPassword;
        $err = $currentUser->modify();
        if ($err) {
            throw new Exception("IDENT0202", $err);
        }

        return $response;
    }
}
