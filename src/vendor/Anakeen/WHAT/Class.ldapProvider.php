<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * ldap authentication provider
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */

include_once ("WHAT/Class.Provider.php");
class ldapProvider extends Provider
{
    public function validateCredential($username, $password)
    {
        
        $host = ($this->parms{'host'} != '' ? $this->parms{'host'} : '127.0.0.1');
        $port = ($this->parms{'port'} != '' ? $this->parms{'port'} : '389');
        $ssl = (strtolower($this->parms{'ssl'}) == 'y' ? true : false);
        $dnbase = ($this->parms{'dn'} != '' ? $this->parms{'dn'} : '%s');
        
        $uri = sprintf("%s://%s:%s/", ($ssl ? 'ldaps' : 'ldap') , $host, $port);
        $r = ldap_connect($uri);
        $err = ldap_get_option($r, LDAP_OPT_PROTOCOL_VERSION, $ret);
        if (!$err) {
            error_log("[$ret] Can't establish LDAP connection : $uri");
            $this->errno = 0;
            return FALSE;
        }
        $opts = $this->parms{'options'};
        foreach ($opts as $k => $v) {
            ldap_set_option($r, $k, $v);
        }
        
        $dn = sprintf($dnbase, $username);
        $b = @ldap_bind($r, $dn, $password);
        if ($b) {
            $this->errno = 0;
            return TRUE;
        } else {
            $err = ldap_error($r);
            error_log("user=[$dn] pass=[*********] result=>" . ($b ? "OK" : "NOK") . " ($err)");
        }
        $this->errno = 0;
        return FALSE;
    }
    /**
     * @param User $whatuser
     * @param string $username
     * @param string $password
     * @return string error message
     */
    public function initializeUser(&$whatuser, $username, $password)
    {
        global $action;
        $err = "";
        
        $CoreNull = "";
        $core = new Application();
        $core->Set("CORE", $CoreNull);
        $core->session = new Session();
        $action = new Action();
        $action->Set("", $core);
        $action->user = new Account("", 1); //create user as admin
        $whatuser->firstname = '--';
        $whatuser->lastname = '(from ldap) ' . $username;
        $whatuser->login = $username;
        $whatuser->password_new = uniqid("ldap");
        $whatuser->famid = "IUSER";
        $err = $whatuser->Add();
        error_log("What user $username added (id=" . $whatuser->id . ")");
        if ($err != "") {
            $this->errno = 0;
            return sprintf(_("cannot create user %s: %s") , $username, $err);
        }
        
        include_once ("FDL/Class.DocFam.php");
        $dbaccess = getDbAccess();
        $du = new_doc($dbaccess, $whatuser->fid);
        if ($du->isAlive()) {
            $du->setValue("us_whatid", $whatuser->id);
            $err = $du->modify();
            if ($err == "") {
                error_log("User $username added (id=" . $du->id . ")");
                if ($this->parms{'dGroup'} != '') {
                    /**
                     * @var Dir $gu
                     */
                    $gu = new_Doc($dbaccess, $this->parms{'dGroup'});
                    if ($gu->isAlive()) {
                        $errg = $gu->addFile($du->id);
                        if ($errg == "") error_log("User $username added to group " . $this->parms{'dGroup'});
                    }
                }
            }
        } else {
            sprintf(_("cannot create user %s: %s") , $username, $err);
        }
        $core->session->close();
        
        $this->errno = 0;
        return $err;
    }
}
