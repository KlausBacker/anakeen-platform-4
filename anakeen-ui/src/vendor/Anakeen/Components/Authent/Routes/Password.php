<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Components\Authent\Routes;

use Anakeen\Core\ContextManager;
use Anakeen\Core\SEManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use SmartStructure\Iuser;

/**
 * Class Password
 * Change user password
 * @note    Used by route : PUT /api/v2/authent/password/{login}
 * @package Anakeen\Routes\Authent
 */
class Password
{
    /**
     * Reset password
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     * @return \Slim\Http\response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $userIdentifier = $args["login"];
        $password = $request->getParam("password");

        $currentUser = ContextManager::getCurrentUser();


        if ($currentUser->login !== $userIdentifier && $currentUser->mail !== $userIdentifier) {
            $e = new Exception("AUTH0020");
            $e->setUserMessage(___("Invalid key to update password", "authent"));
            throw $e;
        }

        /**
         * @var Iuser $udoc
         */
        $udoc = SEManager::getDocument($currentUser->fid);

        if ($udoc) {
            $err = $udoc->testForcePassword($password);
            if ($err) {
                throw new Exception("AUTH0021", $err);
            }
        }

        $currentUser->password_new = $password;
        $err = $currentUser->modify();
        if ($err) {
            throw new Exception("AUTH0021", $err);
        }

        return ApiV2Response::withData($response, ["message" => sprintf(___("Password has been reset for \"%s\"", "authent"), $currentUser->getAccountName())]);
    }
}
