<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Authent;

use Anakeen\Core\Internal\ContextParameterManager;
use Anakeen\Core\Settings;
use Anakeen\Router\AuthenticatorManager;
use Anakeen\Router\Exception;
use Anakeen\Core\LogException;
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


        if (!isset($password) && ($password  === "0" || empty($password))) {
            sleep(self::FAILDELAY);
            $e = new Exception('AUTH0001', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            throw $e;
        }
        $user = new \Anakeen\Core\Account();
        $user->setLoginName($login);
        $result = false;
        if ($user->isAffected()) {
            try {
                $result = $user->checkpassword($password);
            } catch (\Exception $e) {
                LogException::writeLog($e);
                sleep(self::FAILDELAY);
                $e = new Exception('AUTH0001', __METHOD__);
                $e->setHttpStatus('403', 'Forbidden');
                throw $e;
            }
        } else {
            if (!$user->isAffected()) {
                sleep(self::FAILDELAY);
                $e = new Exception('AUTH0023', __METHOD__);
                $e->setHttpStatus('403', 'Forbidden');
                $e->setUserMessage(Gettext::___("Username and/or password is incorrect", "authent"));
                throw $e;
            }
        }
        if (!$result) {
            sleep(self::FAILDELAY);
            $e = new Exception('AUTH0023', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            $e->setUserMessage(Gettext::___("Username and/or password is incorrect", "authent"));
            throw $e;
        }
        $_SERVER['PHP_AUTH_USER'] = $login;


        $session = new \Anakeen\Core\Internal\Session();
        $session->set();
        $session->register('username', $login);
        if ($language) {
            $u = new \Anakeen\Core\Account();
            $u->setLoginName($login);
            \Anakeen\Core\ContextManager::initContext($u, AuthenticatorManager::$session);
            ContextParameterManager::setUserValue(Settings::NsSde, "CORE_LANG", $language);
        }
        AuthenticatorManager::$auth->auth_session=$session;
        $status = AuthenticatorManager::checkAccess(null, true);

        if ($status !== AuthenticatorManager::AccessOk) {
            $e = new Exception('AUTH0002', $status);
            $e->setHttpStatus('403', 'Forbidden');
            $e->setUserMessage(___("Account access not granted, please contact your system administrator", "authent"));
            throw $e;
        }

        return ApiV2Response::withData($response, ["login" => $login]);
    }
}
