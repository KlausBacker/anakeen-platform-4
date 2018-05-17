<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Specials methods for Role family
 *
 */
namespace Anakeen\SmartStructures\Role;

use Anakeen\Core\DbManager;
use Anakeen\SmartHooks;

class RoleHooks extends \Anakeen\SmartStructures\Document
{
    /**
     * @var \Anakeen\Core\Account system role
     */
    protected $sysRole = null;
    

    public function preUpdate()
    {
        parent::preUpdate();
        if ($this->isChanged()) {
            $this->lowerLogin();
        }
    }

    /**
     * return concatenation of mail addresses
     * @param bool $rawmail if true get mail address only else get mail address with name
     * @return string
     */
    public function getMail($rawmail = false)
    {
        $wu = $this->getAccount();
        if ($wu->isAffected()) {
            return $wu->getMail($rawmail);
        }
        return '';
    }
    private function lowerLogin()
    {
        $login = $this->getRawValue("role_login");
        if (mb_strtolower($login) != $login) {
            $this->setValue("role_login", mb_strtolower($login));
        }
    }


    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            /**
             * synchro with User table
             *
             * @return string error message, if no error empty string
             */
            $err = $this->userSynchronize();
            return $err;
        });
        $this->getHooks()->addListener(SmartHooks::PRECREATED, function () {
            $this->lowerLogin();
            $err = $this->userSynchronize();
            return $err;
        })->addListener(SmartHooks::PREUNDELETE, function () {
            return _("role cannot be revived");
        })->addListener(SmartHooks::POSTDELETE, function () {
            $role = $this->getAccount();
            if ($role) {
                $role->Delete();
            }
        });
    }

    /**
     * update/create system role from document role
     * @return string error message
     */
    protected function userSynchronize()
    {
        $err = '';
        if ($this->isAffected()) {
            $sR = $this->getAccount();
            
            if (!$sR) {
                // try create it
                $sR = new \Anakeen\Core\Account();
                $sR->login = $this->getRawValue('role_login');
                $sR->lastname = $this->getRawValue('role_name');
                $sR->fid = $this->initid;
                $sR->accounttype = \Anakeen\Core\Account::ROLE_TYPE;
                $sR->password_new = uniqid("role");

                $err = $sR->add();
                if ($err == "") {
                    $this->setValue("us_whatid", $sR->id);
                    $this->modify(true, array(
                        "us_whatid"
                    ), true);
                    $this->refreshDocUser();
                }
            } else {
                // update it
                $sR->login = $this->getRawValue('role_login');
                $sR->lastname = $this->getRawValue('role_name');
                $sR->fid = $this->initid;
                $err = $sR->modify();
            }
        }
        
        return $err;
    }
    /**
     * recompute sytstem values from USER database
     */
    public function refreshDocUser()
    {
        $wid = $this->getRawValue("us_whatid");
        if ($wid > 0) {
            $wuser = $this->getAccount(true);
            
            if ($wuser && $wuser->isAffected()) {
                $this->SetValue("us_whatid", $wuser->id);
                $this->SetValue("role_login", $wuser->login);
                $this->SetValue("role_name", $wuser->lastname);
                $this->modify(true, "", true);
            }
        }
    }
    /**
     * return system user object conform to whatid
     *
     * @param bool $nocache
     *
     * @return \Anakeen\Core\Account|null return null if not found
     */
    public function getAccount($nocache = false)
    {
        if ($nocache) {
            unset($this->sysRole); // needed for reaffect new values
            $this->sysRole = null;
        }
        if (empty($this->sysRole)) {
            $wid = $this->getRawValue("us_whatid");
            if ($wid > 0) {
                $this->sysRole = new \Anakeen\Core\Account("", $wid);
            }
        }
        if (!$this->sysRole) {
            return null;
        }
        return $this->sysRole;
    }
    /**
     * constraint to detect unique login
     * @param $login
     * @return string
     */
    public function isUniqueLogin($login)
    {
        $err = "";
        $sql = sprintf("select id from users where login = '%s' and id != %d", mb_strtolower(pg_escape_string($login)), $this->getRawValue("us_whatid"));
        DbManager::query($sql, $id, true, true);
        
        if ($id) {
            $err = sprintf(_("role %s id is already used"), $login);
        }
        
        return $err;
    }
}
