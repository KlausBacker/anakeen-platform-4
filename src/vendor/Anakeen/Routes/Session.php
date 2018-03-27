<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Authent;

use Anakeen\Router\Exception;
use Anakeen\Core\LogException;
use Anakeen\Router\ApiV2Response;

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
     * @throws \Dcp\ApplicationParameterManager\Exception
     */
    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {

        $login = $args["login"];
        $password = $request->getParam("password");
        $language = $request->getParam("language");


        if (empty($password)) {
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
                $e = new Exception('AUTH0001', __METHOD__);
                $e->setHttpStatus('403', 'Forbidden');
                throw $e;
            }
        }
        if (!$result) {
            sleep(self::FAILDELAY);
            $e = new Exception('AUTH0001', __METHOD__);
            $e->setHttpStatus('403', 'Forbidden');
            throw $e;
        }
        $_SERVER['PHP_AUTH_USER'] = $login;
        $session = new \Session();
        $session->set();
        $session->register('username', $login);
        if ($language) {
            $u = new \Anakeen\Core\Account();
            $u->setLoginName($login);

            \Anakeen\Core\ContextManager::initContext($u, "CORE", "", \AuthenticatorManager::$session);
            \Anakeen\Core\Internal\ApplicationParameterManager::setUserParameterValue("CORE", "CORE_LANG", $language);
        }

        return ApiV2Response::withData($response, ["login" => $login]);
    }
}
