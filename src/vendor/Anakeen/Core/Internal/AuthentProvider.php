<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * provider abstract class
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */

namespace Anakeen\Core\Internal;

/**
 * Class Provider
 * @method initializeUser($a)
 */
abstract class AuthentProvider
{
    public $parms;
    public $pname;
    public $errno;
    
    const ERRNO_BUG_639 = 1;
    
    public function __construct($authprovider, $parms)
    {
        $this->parms = $parms;
        $this->pname = strtolower($authprovider);
        $this->errno = 0;
    }
    
    abstract public function validateCredential($username, $password);
    
    public function validateAuthorization($opt)
    {
        return true;
    }
    
    public function canICreateUser()
    {
        if (array_key_exists('allowAutoFreedomUserCreation', $this->parms) && strtolower($this->parms{'allowAutoFreedomUserCreation'}) == 'yes' && is_callable(array(
            $this,
            'initializeUser'
        ))) {
            $this->errno = 0;
            return true;
        }
        $this->errno = 0;
        return false;
    }
}
