<?php
/**
 * InternalProvider class
 *
 * This class provides methods for authentication based on internal database
 * @author Anakeen
 */
/**
 */
namespace Anakeen\Core\Internal\Authent;

class InternalProvider extends \Anakeen\Core\Internal\AuthentProvider
{
    /**
     * checks user login and password
     *
     * @param string $username user login
     * @param string $password user password
     * @return bool true if ok
     */
    public function validateCredential($username, $password)
    {
        $user = new \Anakeen\Core\Account();
        if ($user->setLoginName($username)) {
            return $user->checkpassword($password);
        }
        return false;
    }
}
