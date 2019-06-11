<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Authent;

use Anakeen\Core\ContextManager;
use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Settings;
use Anakeen\Router\AuthenticatorManager;
use Anakeen\Router\Exception;
use Anakeen\Router\ApiV2Response;
use Anakeen\Core\Utils\Gettext;

/**
 * Class Session
 * Create a user session
 *
 * @note    Used by route : POST /api/v2/authent/sessions/{login}
 * @package Anakeen\Routes\Authent
 */
class Session
{
    const FAILDELAY = 2;

    /**
     * Create User Session after verify authentication
     *
     * @param \Slim\Http\request  $request
     * @param \Slim\Http\response $response
     * @param                     $args
     *
     * @return \Slim\Http\response $response
     * @throws Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $login = $args["login"];
        $password = $request->getParam("password");
        $language = $request->getParam("language");


        if (!isset($password) || $password === "") {
            sleep(self::FAILDELAY);
            $e = new Exception('AUTH0001', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            throw $e;
        }

        $_SERVER['PHP_AUTH_USER'] = $login;
        $_SERVER['PHP_AUTH_PW'] = $password;

        if ($language) {
            ContextManager::setLanguage($language);
            $u = new \Anakeen\Core\Account();
            $u->setLoginName($login);
            if ($u->isAffected()) {
                \Anakeen\Core\ContextManager::initContext($u, AuthenticatorManager::$session);
                ContextParameterManager::setUserValue(Settings::NsSde, "CORE_LANG", $language);
            }
        }
        $status = AuthenticatorManager::checkAccess(null, true);

        if ($status !== AuthenticatorManager::AccessOk) {
            sleep(self::FAILDELAY);
            $e = new Exception('AUTH0002', $status);
            $e->setHttpStatus('403', 'Forbidden');
            $e->setUserMessage(Gettext::___("Username and/or password is incorrect", "authent"));
            throw $e;
        }

        return ApiV2Response::withData($response, ["login" => $login]);
    }
}
