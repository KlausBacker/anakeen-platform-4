<?php

class apacheAuthenticator extends Authenticator
{
    public function checkAuthentication()
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['REMOTE_USER']) || empty($_SERVER['PHP_AUTH_PW']) || ($_SERVER['PHP_AUTH_USER'] !== $_SERVER['REMOTE_USER'])) {
            header('HTTP/1.0 403 Forbidden');
            echo _("User must be authenticate");
            echo "\nApache authentication module does not seems to be active.";
            return self::AUTH_NOK;
        }
        return self::AUTH_OK;
    }
    
    public function checkAuthorization($opt)
    {
        return true;
    }
    
    public function askAuthentication($args)
    {
        header('HTTP/1.0 403 Forbidden');
        echo _("User must be authenticate");
        echo "\nApache authentication module does not seems to be active.";
    }
    
    public function getAuthUser()
    {
        return $_SERVER['PHP_AUTH_USER'];
    }
    
    public function getAuthPw()
    {
        return $_SERVER['PHP_AUTH_PW'];
    }
    
    public function logout($redir_uri = '')
    {
        if ($redir_uri == '') {
            $pUri = parse_url($_SERVER['REQUEST_URI']);
            if (preg_match(':(?P<path>.*/)[^/]*$:', $pUri['path'], $m)) {
                $redir_uri = $m['path'];
            }
        }
        header('Location: ' . $redir_uri);
        return true;
    }
    
    public function setSessionVar($name, $value)
    {
        return true;
    }
    
    public function getSessionVar($name)
    {
        return '';
    }
}
