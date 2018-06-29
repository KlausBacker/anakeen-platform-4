<?php
/**
 * htmlAuthenticator class
 *
 * This class provides methods for HTML form based authentication
 *
 */


namespace Anakeen\Core\Internal;
class HtmlAuthenticator extends Authenticator
{
    public $auth_session = null;
    /*
     * Store the current authenticating user
    */
    private $username = '';

    /**
     **
     **
     *
     */
    public function checkAuthentication()
    {
        $session = $this->getAuthSession();

        $this->username = $session->read('username');

        if ($this->username != "") {
            return Authenticator::AUTH_OK;
        }

        if (!array_key_exists($this->parms['username'], $_POST)) {
            return Authenticator::AUTH_ASK;
        }
        if (!array_key_exists($this->parms['password'], $_POST)) {
            return Authenticator::AUTH_ASK;
        }

        $this->username = getHttpVars($this->parms['username']);
        if (is_callable(array(
            $this->provider,
            'validateCredential'
        ))) {
            if (!$this->provider->validateCredential(getHttpVars($this->parms['username']), getHttpVars($this->parms{'password'}))) {
                return Authenticator::AUTH_NOK;
            }

            if (!$this->documentUserExists(getHttpVars($this->parms['username']))) {
                if (!$this->tryInitializeUser(getHttpVars($this->parms['username']))) {
                    return Authenticator::AUTH_NOK;
                }
            }
            $session->register('username', getHttpVars($this->parms['username']));
            $session->setuid(getHttpVars($this->parms['username']));
            return Authenticator::AUTH_OK;
        }

        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: " . get_class($this->provider) . " must implement function validateCredential()");
        return Authenticator::AUTH_NOK;
    }

    /**
     * retrieve authentication session
     *
     * @return \Anakeen\Core\Internal\Session the session object
     */
    public function getAuthSession()
    {
        if (!$this->auth_session) {
            $this->auth_session = new \Anakeen\Core\Internal\Session(\Anakeen\Core\Internal\Session::PARAMNAME);
            if (array_key_exists(\Anakeen\Core\Internal\Session::PARAMNAME, $_COOKIE)) {
                $this->auth_session->Set($_COOKIE[\Anakeen\Core\Internal\Session::PARAMNAME]);
            } else {
                $this->auth_session->Set();
            }
        }

        return $this->auth_session;
    }


    public function checkAuthorization($opt)
    {
        if (is_callable(array(
            $this->provider,
            'validateAuthorization'
        ))) {
            return $this->provider->validateAuthorization($opt);
        }
        return true;
    }


    public function askAuthentication($args)
    {
        $session = $this->getAuthSession();
        /* Force removal of username if it already exists on the session */
        $session->register('username', '');
        $session->setuid(\Anakeen\Core\Account::ANONYMOUS_ID);
        $args = [];
        if (!isset($args['redirect_uri'])) {
            if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] !== "/") {
                $args['redirect_uri'] = $_SERVER['REQUEST_URI'];
            }
        }

        header(sprintf('Location: %s', $this->getAuthUrl($args)));
        return true;
    }

    /**
     * return url used to connect user
     *
     * @param array $extendedArg
     *
     * @throws \Dcp\Exception
     * @return string
     */
    public function getAuthUrl(array $extendedArg = array())
    {
        if (empty($this->parms['auth']['app'])) {
            throw new \Dcp\Exception("Missing html/auth/app config.");
        }
        $hasArgs = false;
        $location = \Anakeen\Core\Internal\Session::getWebRootPath();
        $location .= "./login/";
        if (!empty($this->parms['auth']['args'])) {
            $location .= '?' . $this->parms['auth']['args'];
            $hasArgs = true;
        }
        $sargs = '';
        foreach ($extendedArg as $k => $v) {
            $sargs .= sprintf("%s%s=%s", $hasArgs ? '&' : '?', $k, urlencode($v));
            $hasArgs = true;
        }
        return $location . $sargs;
    }

    /**
     * ask authentication and redirect
     *
     * @param string $uri uri to redirect after connection
     */
    public function connectTo($uri)
    {
        $location = sprintf('%s&redirect_uri=%s', $this->getAuthUrl(), urlencode($uri));
        header(sprintf('Location: %s', $location));
        exit(0);
    }

    public function getAuthUser()
    {
        $session_auth = $this->getAuthSession();
        $username = $session_auth->read('username');
        if ($username != '') {
            return $username;
        }
        return $this->username;
    }

    public function getAuthPw()
    {
        return null;
    }

    public function logout($redir_uri = '')
    {
        $session_auth = $this->getAuthSession();
        if (array_key_exists(\Anakeen\Core\Internal\Session::PARAMNAME, $_COOKIE)) {
            $session_auth->close();
        }
        if ($redir_uri == "") {
            if (isset($this->parms['auth']['app'])) {
                header('Location: ' . $this->getAuthUrl());
                return true;
            }
            $redir_uri = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_BASEURL");
        }
        header('Location: ' . $redir_uri);
        return true;
    }


    public function setSessionVar($name, $value)
    {
        $session_auth = $this->getAuthSession();
        $session_auth->register($name, $value);

        return $session_auth->read($name);
    }


    public function getSessionVar($name)
    {
        $session_auth = $this->getAuthSession();
        return $session_auth->read($name);
    }

    public function logon()
    {

        throw new \Exception(__METHOD__);

    }
}
