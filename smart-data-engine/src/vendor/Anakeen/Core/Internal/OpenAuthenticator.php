<?php
/**
 * OpenAuthenticator class
 *
 * This class provides methods for private key based authentification
 *
 */

namespace Anakeen\Core\Internal;

/**
 * Class OpenAuthenticator
 * @package Anakeen\Core\Internal
 */
class OpenAuthenticator extends Authenticator
{
    const waitDelayError = 1;
    const openAuthorizationScheme = "AnkToken";
    const openGetId = "ank-authorization";
    private $privatelogin = false;
    public $token;
    public $auth_session = null;

    /**
     * no need to ask authentication
     */
    public function checkAuthentication()
    {
        $privatekey = static::getTokenId();
        if (!$privatekey) {
            return Authenticator::AUTH_NOK;
        }
        $this->privatelogin = $this->getLoginFromPrivateKey($privatekey);
        if ($this->privatelogin === false) {
            sleep(self::waitDelayError);
            return Authenticator::AUTH_NOK;
        }

        $err = $this->consumeToken($privatekey);
        if ($err === false) {
            return Authenticator::AUTH_NOK;
        }

        $session = $this->getAuthSession();
        $session->register('username', $this->getAuthUser());
        $session->setuid($this->getAuthUser());
        return Authenticator::AUTH_OK;
    }

    public static function getTokenId()
    {
        $tokenId = $_REQUEST[self::openGetId] ?? ($_REQUEST["privateid"] ?? '');
        if (!$tokenId) {
            $hAuthorization = \Anakeen\Router\AuthenticatorManager::getAuthorizationValue();

            if (!empty($hAuthorization)) {
                if ($hAuthorization["scheme"] === self::openAuthorizationScheme) {
                    $tokenId = $hAuthorization["token"];
                }
            }
        }
        return $tokenId;
    }

    public static function getLoginFromPrivateKey($privatekey)
    {
        $token = static::getUserToken($privatekey);
        if ($token === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Token '%s' not found.", $privatekey));
            return false;
        }

        $uid = $token->userid;
        $user = new \Anakeen\Core\Account('', $uid);
        if (!is_object($user) || !$user->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Could not get user with uid '%s' for token '%s'.", $uid, $privatekey));
            return false;
        }

        if (!static::verifyOpenAccess($token)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Access deny for user '%s' with token '%s' : context not match.", $user->login, $privatekey));

            return false;
        }

        if (!static::verifyOpenExpire($token)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Access deny for user '%s' with token '%s' : token has expired.", $user->login, $privatekey));

            return false;
        }
        return $user->login;
    }

    public static function getUserToken($tokenId)
    {
        $token = new \UserToken('', $tokenId);
        if (!is_object($token) || !$token->isAffected()) {
            return false;
        }

        return $token;
    }

    public static function verifyOpenExpire(\UserToken $token)
    {
        $expiredate = $token->expire;
        if ($expiredate === "infinity") {
            return true;
        }
        $date = new \DateTime($expiredate);
        $now = new \DateTime();

        return $now <= $date;
    }

    public static function verifyOpenAccess(\UserToken $token)
    {
        $rawContext = $token->context;

        $allow = false;

        if ($token->type && $token->type !== "CORE") {
            return false;
        }

        if ($rawContext === null) {
            return true;
        }

        if ($rawContext) {
            $context = unserialize($rawContext);
            if (is_array($context)) {
                $allow = true;
                foreach ($context as $k => $v) {
                    if (isset($_REQUEST[$k]) && ($_REQUEST[$k] !== (string)$v)) {
                        $allow = false;
                    }
                }
            }
        }

        return $allow;
    }

    public function consumeToken($privatekey)
    {

        $token = new \UserToken('', $privatekey);
        if (!is_object($token) || !$token->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Token '%s' not found.", $privatekey));
            return false;
        }

        $this->token = $token->getValues();
        if ($token->expendable === 't') {
            $token->delete();
        }

        return $privatekey;
    }

    public function checkAuthorization($opt)
    {
        return true;
    }

    /**
     * no ask
     * @param $args
     * @return bool
     */
    public function askAuthentication($args)
    {
        header("HTTP/1.0 403 Forbidden", true);
        print ___("Private key identifier is not valid", "authentOpen");

        return true;
    }

    public function getAuthUser()
    {
        return $this->privatelogin;
    }

    /**
     * no password needed
     */
    public function getAuthPw()
    {
        return false;
    }

    /**
     * no logout
     * @param string $redir_uri
     * @return bool
     */
    public function logout($redir_uri = '')
    {
        header("HTTP/1.0 401 Authorization Required");
        print ___("Authorization Required", "authentOpen");
        return true;
    }

    public function setSessionVar($name, $value)
    {
        $session = $this->getAuthSession();
        $session->register($name, $value);
        return $session->read($name);
    }

    public function getSessionVar($name)
    {
        $session = $this->getAuthSession();
        return $session->read($name);
    }

    public function getAuthSession()
    {
        if (!$this->auth_session) {
            $this->auth_session = new \Anakeen\Core\Internal\Session();
            $this->auth_session->useCookie(false);
            $this->auth_session->Set();
        }
        return $this->auth_session;
    }
}
