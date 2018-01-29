<?php
namespace Dcp\HttpApi\V1;

class ContextManager
{
    /**
     * Control user has a good session
     * Complete AuthenticatorManager singleton
     * @deprecated use \Dcp\Core\ContextManager::authent
     * @throws Api\Exception
     */
    public static function controlAuthent()
    {
        // Ask authentification if HTML required
        $urlInfo = parse_url($_SERVER["REQUEST_URI"]);
        $headers = apache_request_headers();
        $askAuthent = (preg_match("/\\.html$/", $urlInfo["path"]) || (!empty($headers["Accept"]) && preg_match("@\\btext/html\\b@", $headers["Accept"])));
        
        $status = AuthenticatorManager::checkAccess(null, !$askAuthent);
        
        switch ($status) {
            case \Authenticator::AUTH_OK: // it'good, user is authentified
                break;

            default:
                $auth = AuthenticatorManager::$auth;
                if ($auth === false) {
                    $exception = new \Dcp\HttpApi\V1\Api\Exception("Could not get authenticator");
                    $exception->setHttpStatus("500", "Could not get authenticator");
                    $exception->setUserMessage("Could not get authenticator");
                    throw $exception;
                }
                
                $exception = new \Dcp\HttpApi\V1\Api\Exception("User must be authenticated");
                $exception->setHttpStatus("403", "Forbidden");
                throw $exception;
        }
        $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();
        // First control
        if (empty($_SERVER['PHP_AUTH_USER'])) {
            $exception = new \Dcp\HttpApi\V1\Api\Exception("User must be authenticated");
            $exception->setHttpStatus("403", "Forbidden");
            throw $exception;
        }
    }
    
    public static function initCoreApplication()
    {
        $u=\Dcp\Core\ContextManager::authentUser();

        \Dcp\Core\ContextManager::initContext($u, "HTTPAPI_V1", "", \AuthenticatorManager::$session);
    }
    /**
     * @return \Action
     */
    public static function getCoreAction()
    {
        return \Dcp\Core\ContextManager::getCurrentAction();
    }
}
