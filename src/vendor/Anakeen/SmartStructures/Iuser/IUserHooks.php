<?php
/*
 * @author Anakeen
 * @package FDL
*/

/**
 * User account document
 *
 */

namespace Anakeen\SmartStructures\Iuser;

use Anakeen\Core\ContextManager;
use Anakeen\Core\DbManager;
use Anakeen\Core\SEManager;
use Anakeen\SmartHooks;
use SmartStructure\Attributes\Iuser as MyAttributes;

/**
 * Class UserAccount
 */
class IUserHooks extends \Anakeen\SmartElement
 implements \Anakeen\Core\IMailRecipient
{
    use TAccount;


    public function preRefresh()
    {
        $err = parent::preRefresh();

        if ($this->getRawValue("US_STATUS") == 'D') {
            $err .= ($err == "" ? "" : "\n") . _("user is deactivated");
        }

        $iduser = $this->getRawValue("US_WHATID");
        if ($iduser > 0) {
            $user = $this->getAccount();
            if (!$user->isAffected()) {
                return sprintf(_("user #%d does not exist"), $iduser);
            }
        } else {
            if ($this->getRawValue("us_login") != '-') {
                $err = _("user has not identificator");
            }
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
             */
            $oa = $this->getAttribute("us_passwd1");
            if ($oa) {
                $oa->needed = true;
            }
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
             */
            $oa = $this->getAttribute("us_passwd2");
            if ($oa) {
                $oa->needed = true;
            }
            $oa = $this->getAttribute("us_tab_system");
            $oa->setOption("firstopen", "yes");
        }
        $this->updateIncumbents();
        return $err;
    }

    public function updateIncumbents()
    {
        $u = $this->getAccount();
        if ($u) {
            $this->setValue("us_incumbents", $u->getIncumbents(false));
        }
    }

    /**
     * get all direct group document identificators of the isuser
     *
     * @return array of group document id, the index of array is the system identifier
     * @throws \Dcp\Db\Exception
     */
    public function getUserGroups()
    {
        DbManager::query(sprintf(
            "SELECT id, fid from users, groups where groups.iduser=%d and users.id = groups.idgroup;",
            $this->getRawValue("us_whatid")
        ), $groupIds, false, false);

        $gids = array();
        foreach ($groupIds as $gid) {
            $gids[$gid["id"]] = $gid["fid"];
        }
        return $gids;
    }

    /**
     * return all direct group and parent group document identificators of $gid
     *
     * @param string $gid systeme identifier group or users
     *
     * @return array
     * @throws \Dcp\Db\Exception
     */
    protected function getAscendantGroup($gid)
    {
        $groupIds = array();
        if ($gid > 0) {
            DbManager::query(sprintf("SELECT id, fid from users, groups where groups.iduser=%d and users.id = groups.idgroup;", $gid), $groupIds, false, false);
            $gids = array(); // current level
            $pgids = array(); // fathers
            foreach ($groupIds as $gid) {
                $gids[$gid["id"]] = $gid["fid"];
            }

            foreach ($gids as $systemGid => $docGid) {
                $pgids += $this->getAscendantGroup($systemGid);
            }
            $groupIds = $gids + $pgids;
        }
        return $groupIds;
    }

    /**
     * get all direct group and parent group document identificators of the isuser
     *
     * @return int[] of group document id the index of array is the system identifier
     * @throws \Dcp\Db\Exception
     */
    public function getAllUserGroups()
    {
        return $this->getAscendantGroup($this->getRawValue("us_whatid"));
    }

    /**
     * Refresh folder parent containt
     */
    public function refreshParentGroup()
    {
        $tgid = $this->getMultipleRawValues("US_IDGROUP");
        foreach ($tgid as $gid) {
            /**
             * @var \SmartStructure\Igroup $gdoc
             */
            $gdoc = SEManager::getDocument($gid);
            if ($gdoc && $gdoc->isAlive()) {
                $gdoc->insertGroups();
            }
        }
    }

    /**
     * recompute intranet values from USER database
     */
    public function refreshDocUser()
    {
        $err = "";
        $wid = $this->getRawValue("us_whatid");
        if ($wid > 0) {
            $wuser = $this->getAccount(true);

            if ($wuser->isAffected()) {
                $this->SetValue(MyAttributes::us_whatid, $wuser->id);
                $this->SetValue(MyAttributes::us_lname, $wuser->lastname);
                $this->SetValue(MyAttributes::us_fname, $wuser->firstname);
                $this->SetValue(MyAttributes::us_passwd1, " ");
                $this->SetValue(MyAttributes::us_passwd2, " ");
                $this->SetValue(MyAttributes::us_login, $wuser->login);
                $this->SetValue(MyAttributes::us_status, $wuser->status);
                $this->SetValue(MyAttributes::us_passdelay, $wuser->passdelay);
                $this->SetValue(MyAttributes::us_expires, $wuser->expires);
                $this->SetValue(MyAttributes::us_daydelay, $wuser->passdelay / 3600 / 24);
                if ($wuser->substitute > 0) {
                    $this->setValue(MyAttributes::us_substitute, $wuser->getFidFromUid($wuser->substitute));
                } else {
                    $this->clearValue(MyAttributes::us_substitute);
                }

                $rolesIds = $wuser->getRoles(false);
                $this->clearArrayValues("us_t_roles");
                $this->SetValue("us_roles", $rolesIds);

                $mail = $wuser->getMail();
                if (!$mail) {
                    $this->clearValue(MyAttributes::us_extmail);
                    $this->clearValue(MyAttributes::us_mail);
                } else {
                    $this->SetValue(MyAttributes::us_mail, $mail);
                    $this->SetValue(MyAttributes::us_extmail, $mail);
                }

                if ($wuser->passdelay <> 0) {
                    $this->SetValue(MyAttributes::us_expiresd, strftime("%Y-%m-%d", $wuser->expires));
                    $this->SetValue(MyAttributes::us_expirest, strftime("%H:%M", $wuser->expires));
                } else {
                    $this->SetValue(MyAttributes::us_expiresd, " ");
                    $this->SetValue(MyAttributes::us_expirest, " ");
                }
                // search group of the user
                $g = new \Group("", $wid);
                $tgid = array();
                $tgtitle = array();
                if (count($g->groups) > 0) {
                    $gt = new \Anakeen\Core\Account($this->dbaccess);
                    foreach ($g->groups as $gid) {
                        $gt->select($gid);
                        $tgid[] = $gt->fid;
                        $tgtitle[] = $this->getTitle($gt->fid);
                    }
                    $this->clearArrayValues(MyAttributes::us_groups);
                    $this->SetValue(MyAttributes::us_idgroup, $tgid);
                    $this->SetValue(MyAttributes::us_group, $tgtitle);
                } else {
                    $this->clearArrayValues(MyAttributes::us_groups);
                }
                $err = $this->modify();
            } else {
                $err = sprintf(_("user %d does not exist"), $wid);
            }
        }

        return $err;
    }

    /**
     * affect to default group
     */
    public function setToDefaultGroup()
    {
        $grpid = $this->getFamilyParameterValue("us_defaultgroup");
        $err = '';
        if ($grpid) {
            /**
             * @var \SmartStructure\Igroup $grp
             */
            $grp = SEManager::getDocument($grpid);
            if ($grp && $grp->isAlive()) {
                $err = $grp->insertDocument($this->initid);
            }
        }
        return $err;
    }

    protected function updateExpireDate()
    {
        $err = "";
        /**
         * @var \Anakeen\Core\Internal\Action $action
         */
        global $action;
        $ed = floatval($action->getParam("AUTHENT_ACCOUNTEXPIREDELAY"));
        if ($ed > 0) {
            $expdate = time() + ($ed * 24 * 3600);
            $err = $this->SetValue("us_accexpiredate", strftime("%Y-%m-%d 00:00:00", $expdate));
            if ($err == '') {
                $err = $this->modify(true, array("us_accexpiredate"), true);
            }
        }

        return $err;
    }

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            /**
             * update/synchro system user
             */
            $err = $this->synchronizeSystemUser();
            if (!$err) {
                $this->refreshRoles();
            }
            return $err;
        });

        $this->getHooks()->addListener(SmartHooks::POSTCREATED, function () {
            return $this->updateExpireDate();
        })->addListener(SmartHooks::PREUNDELETE, function () {
            return _("user cannot be revived");
        })->addListener(SmartHooks::POSTDELETE, function () {
            $user = $this->getAccount();
            if ($user) {
                $user->Delete();
            }
        });
    }


    /**
     * Modify system account from document IUSER
     */
    public function synchronizeSystemUser()
    {
        $err = '';
        $lname = $this->getRawValue("us_lname");
        $fname = $this->getRawValue("us_fname");
        $pwd1 = $this->getRawValue("us_passwd1");
        $pwd2 = $this->getRawValue("us_passwd2");
        $daydelay = $this->getRawValue("us_daydelay");
        if ($daydelay == -1) {
            $passdelay = $daydelay;
        } else {
            $passdelay = intval($daydelay) * 3600 * 24;
        }
        $status = $this->getRawValue("us_status");
        $login = $this->getRawValue("us_login");
        $substitute = $this->getRawValue("us_substitute");
        $allRoles = $this->getArrayRawValues("us_t_roles");
        $extmail = $this->getRawValue("us_extmail", " ");

        if ($login != "-") {
            // compute expire for epoch
            $expiresd = $this->getRawValue("us_expiresd");
            $expirest = $this->getRawValue("us_expirest", "00:00");
            //convert date
            $expdate = $expiresd . " " . $expirest . ":00";
            $expires = 0;
            if ($expdate != "") {
                if (preg_match("|([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])|", $expdate, $reg)) {
                    $expires = mktime($reg[4], $reg[5], $reg[6], $reg[2], $reg[1], $reg[3]);
                } elseif (preg_match("|(2[0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])|", $expdate, $reg)) {
                    $expires = mktime($reg[4], $reg[5], $reg[6], $reg[2], $reg[3], $reg[1]);
                }
            }

            $fid = $this->id;
            $newuser = false;
            $user = $this->getAccount();
            if (!$user) {
                $user = new \Anakeen\Core\Account(""); // create new user
                $this->wuser = &$user;
                $newuser = true;
            }
            // get direct system role ids
            $roles = array();
            foreach ($allRoles as $arole) {
                if ($arole["us_rolesorigin"] != "group") {
                    $roles[] = $arole["us_roles"];
                }
            }
            $roleIds = $this->getSystemIds($roles);
            // perform update system User table
            if ($substitute) {
                $substitute = $this->getDocValue($substitute, "us_whatid");
            }
            $err .= $user->updateUser($fid, $lname, $fname, $expires, $passdelay, $login, $status, $pwd1, $pwd2, $extmail, $roleIds, $substitute);
            if ($err == "") {
                if ($user) {
                    $this->setValue(MyAttributes::us_whatid, $user->id);
                    $this->setValue(MyAttributes::us_meid, $this->id);

                    $this->modify(false, array(
                        MyAttributes::us_whatid,
                        MyAttributes::us_meid
                    ));
                    if ($newuser) {
                        $err .= $this->setToDefaultGroup();
                    }
                }
            }

            if ($err == "") {
                $err = $this->RefreshDocUser(); // refresh from core database
            }
        } else {
            // tranfert extern mail if no login specified yet
            if ($this->getRawValue("us_login") == "-") {
                $email = $this->getRawValue("us_extmail");
                if (($email != "") && ($email[0] != "<")) {
                    $this->setValue("us_mail", $email);
                } else {
                    $this->clearValue("us_mail");
                }
            }
        }

        return $err;
    }

    public function preconsultation()
    {
        $this->refreshRoles();
    }

    public function preEdition()
    {
        $allRoles = $this->getArrayRawValues("us_t_roles");
        $this->clearArrayValues("us_t_roles");
        // get direct system role ids
        $roles = array();
        foreach ($allRoles as $arole) {
            if ($arole["us_rolesorigin"] != "group") {
                $roles[] = $arole["us_roles"];
            }
        }
        $this->setValue("us_roles", $roles);
        if ($this->getRawValue("us_whatid") == \Anakeen\Core\Account::ANONYMOUS_ID) {
            // Anonymous has no password
            $passFrame = $this->getAttribute("us_passwd1");
            if ($passFrame) {
                $passFrame->setVisibility("H");
            }
            $passFrame = $this->getAttribute("us_passwd2");
            if ($passFrame) {
                $passFrame->setVisibility("H");
            }
        }
    }

    /**
     * recompute role attributes from system role
     */
    public function refreshRoles()
    {
        $u = $this->getAccount();
        if (!$u) {
            return;
        }
        $directRoleIds = $u->getRoles();
        $allParents = $u->getUserParents();
        $allRoles = $allGroup = array();
        foreach ($allParents as $aParent) {
            if ($aParent["accounttype"] == \Anakeen\Core\Account::ROLE_TYPE) {
                $allRoles[] = $aParent;
            } else {
                $allGroup[] = $aParent;
            }
        }

        $this->clearArrayValues("us_t_roles");
        foreach ($allRoles as $role) {
            if (in_array($role["id"], $directRoleIds)) {
                $group = '';
                $status = 'internal';
                $this->addArrayRow("us_t_roles", array(
                    "us_roles" => $role["fid"],
                    "us_rolesorigin" => $status,
                    "us_rolegorigin" => $group
                ));
            }

            $rid = $role["id"];
            $tgroup = array();
            foreach ($allGroup as $aGroup) {
                DbManager::query(sprintf("select idgroup from groups where iduser=%d and idgroup=%d", $aGroup["id"], $rid), $gr);
                if ($gr) {
                    $tgroup[] = $aGroup["fid"];
                }
            }
            if ($tgroup) {
                $status = 'group';
                $group = implode('<BR>', $tgroup);
                $this->addArrayRow("us_t_roles", array(
                    "us_roles" => $role["fid"],
                    "us_rolesorigin" => $status,
                    "us_rolegorigin" => $group
                ));
            }
        }
    }

    /**
     * return main mail address in RFC822 format
     *
     * @param bool $rawmail if true only system amil address else add also display name
     *
     * @return string
     */
    public function getMail($rawmail = false)
    {
        $wu = $this->getAccount();
        if ($wu && $wu->isAffected()) {
            return $wu->getMail($rawmail);
        }
        return '';
    }

    /**
     * return main mail address in a user-friendly representation
     * (by default we return the getMail() address, and it's up to the
     * descendant to override it and implement it's own user-friendly
     * representation)
     *
     * @return string
     */
    public function getMailTitle()
    {
        return $this->getMail();
    }

    /**
     * return crypted password
     *
     * @return string
     */
    public function getCryptPassword()
    {
        $wu = $this->getAccount();
        if ($wu && $wu->isAffected()) {
            return $wu->password;
        }
        return '';
    }

    public function constraintPassword($pwd1, $pwd2, $login)
    {
        if ($this->testForcePassword($pwd1)) {
            return '';
        }
        $sug = array();
        $err = "";

        if ($pwd1 <> $pwd2) {
            $err = _("the 2 passwords are not the same");
        } elseif (($pwd1 == "") && ($this->getRawValue("us_whatid") == "")) {
            if ($login != "-") {
                $err = _("passwords must not be empty");
            }
        }

        return array(
            "err" => $err,
            "sug" => $sug
        );
    }

    public function testForcePassword($pwd)
    {
        $minLength = intval(ContextManager::getApplicationParam("AUTHENT_PWDMINLENGTH"));
        $minDigitLength = intval(ContextManager::getApplicationParam("AUTHENT_PWDMINDIGITLENGTH"));
        $minUpperLength = intval(ContextManager::getApplicationParam("AUTHENT_PWDMINUPPERALPHALENGTH"));
        $minLowerLength = intval(ContextManager::getApplicationParam("AUTHENT_PWDMINLOWERALPHALENGTH"));
        $minSymbolLength = intval(ContextManager::getApplicationParam("AUTHENT_PWDMINSYMBOLLENGTH"));

        if (preg_match('/[\p{C}]/u', $pwd)) {
            return _("Control characters are not allowed");
        }

        $msg = sprintf(_("Your password is not secure."));
        if ($minLength > 0) {
            $msg .= "\n " . sprintf(_("It must contains at least %d characters (total length)"), $minLength);
        }
        if ($minDigitLength + $minUpperLength + $minLowerLength + $minSymbolLength > 0) {
            $msg .= " " . sprintf(_("with these conditions"));
        }
        if ($minDigitLength) {
            if ($minDigitLength > 1) {
                $msg .= "\n  - " . sprintf(_("at least %d digits"), $minDigitLength);
            } else {
                $msg .= "\n  - " . sprintf(_("at least one digit"));
            }
        }
        if ($minUpperLength) {
            if ($minUpperLength > 1) {
                $msg .= "\n  - " . sprintf(_("at least %d uppercase alpha characters"), $minUpperLength);
            } else {
                $msg .= "\n  - " . sprintf(_("at least one uppercase alpha character"));
            }
        }
        if ($minLowerLength) {
            if ($minLowerLength > 1) {
                $msg .= "\n  - " . sprintf(_("at least %d lowercase alpha characters"), $minLowerLength);
            } else {
                $msg .= "\n  - " . sprintf(_("at least one lowercase alpha character"));
            }
        }
        if ($minSymbolLength) {
            if ($minSymbolLength > 1) {
                $msg .= "\n  - " . sprintf(_("at least %d symbol characters"), $minSymbolLength);
            } else {
                $msg .= "\n  - " . sprintf(_("at least one symbol character"));
            }
        }
        if (mb_strlen($pwd) < $minLength) {
            $err = _("Not enough characters.") . "\n";
            return nl2br($err . $msg);
        }
        $alphanum = 0;

        if ($minDigitLength) {
            preg_match_all('/[0-9]/', $pwd, $matches);
            $alphanum += count($matches[0]);
            if (count($matches[0]) < $minDigitLength) {
                $err = _("Not enough digits.") . "\n";
                return nl2br($err . $msg);
            }
        }
        if ($minUpperLength) {
            preg_match_all('/[\p{Lu}]/u', $pwd, $matches);
            $alphanum += count($matches[0]);
            if (count($matches[0]) < $minUpperLength) {
                $err = _("Not enough uppercase characters.") . "\n";
                return nl2br($err . $msg);
            }
        }
        if ($minLowerLength) {
            preg_match_all('/[\p{Ll}]/u', $pwd, $matches);
            $alphanum += count($matches[0]);
            if (count($matches[0]) < $minLowerLength) {
                $err = _("Not enough lowercase characters.") . "\n";
                return nl2br($err . $msg);
            }
        }
        if ($minSymbolLength) {
            if ((mb_strlen($pwd) - $alphanum) < $minSymbolLength) {
                $err = _("Not enough special characters.") . "\n";
                return nl2br($err . $msg);
            }
        }
        return '';
    }

    /**
     * Constraint to verify expiration data
     *
     * @param $expiresd
     * @param $expirest
     * @param $daydelay
     *
     * @return array
     */
    public function constraintExpires($expiresd, $expirest, $daydelay)
    {
        $err = '';
        $sug = array();
        if (($expiresd <> "") && ($daydelay == 0)) {
            $err = _("Expiration delay must not be 0 to keep expiration date");
        }

        return array(
            "err" => $err,
            "sug" => $sug
        );
    }


    /**
     * Set/change user password
     *
     * @param string $password password to crypt
     *
     * @return string
     */
    public function setPassword($password)
    {
        $idwuser = $this->getRawValue("US_WHATID");

        $wuser = $this->getAccount();
        if (!$wuser->isAffected()) {
            return sprintf(_("user #%d does not exist"), $idwuser);
        }
        // Change what user password
        $wuser->password_new = $password;
        $err = $wuser->modify();
        if ($err != "") {
            return $err;
        }

        return "";
    }

    /**
     * Increase login failure count
     */
    public function increaseLoginFailure()
    {
        if ($this->getRawValue("us_whatid") == 1) {
            return "";
        } // it makes non sense for admin
        $lf = intval($this->getRawValue("us_loginfailure", 0)) + 1;
        $err = $this->SetValue("us_loginfailure", $lf);
        if ($err == "") {
            $this->modify(false, array(
                "us_loginfailure"
            ), false);
        }
        return "";
    }

    /**
     * Reset login failure count
     *
     * @apiExpose
     */
    public function resetLoginFailure()
    {
        if ($this->getRawValue("us_whatid") == 1) {
            return "";
        } // it makes non sense for admin
        $err = $this->canEdit();
        if ($err == '') {
            if (intval($this->getRawValue("us_loginfailure")) > 0) {
                $err = $this->setValue("us_loginfailure", 0);
                if ($err == "") {
                    $err = $this->modify(false, array(
                        "us_loginfailure"
                    ), false);
                }
            }
        }
        return $err;
    }

    /**
     * the incumbent account documents cannot be modified by susbtitutes
     *
     * @param string $aclname
     * @param bool   $strict
     *
     * @return string
     */
    public function control($aclname, $strict = false)
    {
        $u = $this->getAccount();
        if ($u && ($u->substitute == ContextManager::getCurrentUser()->id)) {
            return parent::control($aclname, true);
        } else {
            return parent::control($aclname, $strict);
        }
    }


    public static function parseMail($Email)
    {
        $sug = array(); // suggestions
        $err = "";

        if ($Email != "") {
            if ($Email[0] == "<") {
                $sug[] = _("<it's a message>");
            } else {
                if (preg_match("/^[_\.0-9\/'?$&\+~`%|*a-z=^{}-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,6}$/i", $Email)) {
                    return true;
                }
                $err = _("the email syntax  is like : john.doe@anywhere.org");
                if (!preg_match("/@/", $Email)) {
                    $err = _("the email must containt the @ character");
                }
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }


    /**
     * Manage account security
     */
    public function isAccountActive()
    {
        if ($this->getRawValue("us_whatid") == 1) {
            return false;
        } // it makes non sense for admin
        $u = $this->getAccount();
        if ($u) {
            return $u->status != 'D';
        }
        return false;
    }

    /**
     * @apiExpose
     * @return string error message
     */
    public function activateAccount()
    {
        // Check that the user has FUSERS privileges
        global $action;
        if ($this->canEdit() != '' || !$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return _("current user cannot deactivate account");
        }
        // The 'admin' account cannot be deactivated
        if ($this->getRawValue("us_whatid") == 1) {
            return '';
        }
        $err = $this->SetValue("us_status", 'A');
        if ($err == "") {
            $err = $this->modify(true, array(
                "us_status"
            ), true);
            $this->synchronizeSystemUser();
        }
        return $err;
    }

    public function isAccountInactive()
    {
        return (!$this->isAccountActive());
    }

    /**
     * @apiExpose
     * @return string error message
     */
    public function deactivateAccount()
    {
        // Check that the user has FUSERS privileges
        global $action;
        if ($this->canEdit() != '' || !$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return _("current user cannot deactivate account");
        }
        // The 'admin' account cannot be deactivated
        if ($this->getRawValue("us_whatid") == 1) {
            return '';
        }
        $err = $this->SetValue("us_status", 'D');
        if ($err == "") {
            $err = $this->modify(true, array(
                "us_status"
            ), true);
            $this->synchronizeSystemUser();
        }
        return $err;
    }

    public function accountHasExpired()
    {
        if ($this->getRawValue("us_whatid") == 1) {
            return false;
        }
        $expd = $this->getRawValue("us_accexpiredate");
        //convert date
        $expires = 0;
        if ($expd != "") {
            if (preg_match("|([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9])|", $expd, $reg)) {
                $expires = mktime(0, 0, 0, $reg[2], $reg[1], $reg[3]);
            } elseif (preg_match("|(2[0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9])|", $expd, $reg)) {
                $expires = mktime(0, 0, 0, $reg[2], $reg[3], $reg[1]);
            }
            return ($expires <= time());
        }
        return false;
    }

    /**
     * return attribute used to filter from keyword
     *
     * @return string
     */
    public static function getMailAttribute()
    {
        return "us_mail";
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
