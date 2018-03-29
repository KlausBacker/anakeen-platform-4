<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Group account
 */
namespace Dcp\Core;

use SmartStructure\Attributes\Igroup as MyAttributes;

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/**
 * Class GroupAccount
 *
 */
class GroupAccount extends \SmartStructure\Group
{
    use TAccount;

    public $cviews = array(
        "FUSERS:FUSERS_IGROUP"
    );
    public $eviews = array(
        "USERCARD:CHOOSEGROUP"
    );
    public $exportLdap = array(
        // posixGroup
        "gidNumber" => "GRP_GIDNUMBER",
        //			"mail" => "GRP_MAIL", // not in schema but used in mailing client application
        "description" => "GRP_DESC"
    );
    public $ldapobjectclass = "posixGroup";
    public function preRefresh()
    {
        //  $err=$this->ComputeGroup();
        $err = "";
        $this->AddParamRefresh("US_WHATID", "GRP_MAIL,US_LOGIN");
        // refresh MEID itself
        $iduser = $this->getRawValue("US_WHATID");
        if ($iduser > 0) {
            $user = $this->getAccount();
            if (!$user) {
                return sprintf(_("group #%d does not exist"), $iduser);
            }
        } else {
            return _("group has not identificator");
        }
        
        if ($this->getRawValue("grp_isrefreshed") == "0") {
            $err.= _("this groups must be refreshed");
        }
        return $err;
    }
    public function preUndelete()
    {
        return _("group cannot be revived");
    }
    /**
     * test if the document can be set in LDAP
     */
    public function canUpdateLdapCard()
    {
        return true;
    }
    /**
     * get LDAP title for group
     */
    public function getLDAPTitle()
    {
        return sprintf(_("%s group"), $this->title);
    }
    /**
     * get LDAP array of members
     * @return array
     */
    public function getLDAPMember()
    {
        $g = $this->getAccount();
        $members = $g->getAllMembers();
        $tdn = array();
        foreach ($members as $k => $v) {
            $du = DocManager::getRawDocument($v["fid"]);
            $tdnu = explode("\n", $du["ldapdn"]);
            if (count($tdnu) > 0) {
                $dnu = $tdnu[0];
                if ($dnu) {
                    $tdn[] = $dnu;
                }
            }
        }
        if (count($tdn) == 0) {
            $tdn = "cn=nobody,dc=users," . $this->racine;
        }
        return $tdn;
    }
    /**
     * recompute only parent group
     * call {@see ComputeGroup()}
     * @apiExpose
     *
     * @return string error message, if no error empty string
     */
    public function RefreshGroup()
    {
        //if ($this->norefreshggroup) return '';
        include_once("FDL/Lib.Usercard.php");
        //  $err=_GROUP::RefreshGroup();
        $err = $this->RefreshDocUser();
        //$err.=$this->refreshMembers();
        // refreshGroups(array($this->getRawValue("us_whatid")));
        $err.= $this->insertGroups();
        $err.= $this->Modify();
        //AddWarningMsg(sprintf("RefreshGroup %d %s",$this->id, $this->title));
        if ($err == "") {
            refreshGroups(array(
                $this->getRawValue("us_whatid")
            ), true);
            /*$this->setValue("grp_isrefreshed","1");
             $this->modify(true,array("grp_isrefreshed"),true);*/
        }
        return $err;
    }
    /**
     * Refresh folder parent containt
     */
    public function refreshParentGroup()
    {
        $tgid = $this->getMultipleRawValues("GRP_IDPGROUP");
        foreach ($tgid as $gid) {
            /**
             * @var \SmartStructure\Igroup $gdoc
             */
            $gdoc = DocManager::getDocument($gid);
            if ($gdoc && $gdoc->isAlive()) {
                $gdoc->insertGroups();
            }
        }
    }
    public function postStore()
    {
        return $this->synchronizeSystemGroup();
    }
    /**
     * @deprecated use postStore() instead
     * @return string
     */
    public function postModify()
    {
        deprecatedFunction();
        return self::postStore();
    }
    public function synchronizeSystemGroup()
    {
        $gname = $this->getRawValue("GRP_NAME");
        $login = $this->getRawValue("US_LOGIN");
        $roles = $this->getMultipleRawValues("grp_roles");
        
        $fid = $this->id;
        /**
         * @var \Anakeen\Core\Account $user
         */
        $user = $this->getAccount();
        if (!$user) {
            $user = new \Anakeen\Core\Account(""); // create new user
            $this->wuser = & $user;
        }
        // get system role ids
        $roleIds = $this->getSystemIds($roles);
        $err = $user->SetGroups($fid, $gname, $login, $roleIds);
        if ($err == "") {
            $this->setValue(MyAttributes::us_whatid, $user->id);
            $this->setValue(MyAttributes::us_meid, $this->id);
            $this->modify(false, array(
                MyAttributes::us_whatid,
                MyAttributes::us_meid
            ));
            if ($user) {
                $this->setGroups();
            }
            // get members
            //$this->RefreshGroup(); // in postinsert
            //    $this->refreshParentGroup();
            $wrg = $this->RefreshLdapCard();
            if ($wrg) {
                AddWarningMsg($wrg);
            }
            // add in default folder root groups : usefull for import
            $tgid = $this->getMultipleRawValues("GRP_IDPGROUP");
            $fdoc = $this->getFamilyDocument();
            $dfldid = $fdoc->dfldid;
            if ($dfldid != "") {
                /**
                 * @var \Dir $dfld
                 */
                $dfld = DocManager::getDocument($dfldid);
                if ($dfld && $dfld->isAlive()) {
                    if (count($tgid) == 0) {
                        $dfld->insertDocument($this->initid);
                    } else {
                        $dfld->removeDocument($this->initid);
                    }
                }
            }
            
            $err = $this->refreshMailMembersOnChange();
        }
        
        if ($err == "") {
            $err = "-";
        } // don't do modify after because it is must be set by USER::setGroups
        return $err;
    }
    /**
     * compute the mail of the group
     * concatenation of each user mail and group member mail
     *
     * @param bool $nomail if true no mail will be computed
     * @return void
     */
    public function setGroupMail($nomail = false)
    {
        if (!$nomail) {
            $nomail = ($this->getRawValue("grp_hasmail") == "no");
        }
        if (!$nomail) {
            $this->setValue("grp_mail", $this->getMail());
        } else {
            $this->clearValue('grp_mail');
        }
    }
    /**
     * return concatenation of mail addresses
     * @param bool $rawmail if true only raw address will be returned else complete address with firstname and lastname are returned
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
    /**
     * update LDAP menbers after imodification of containt
     */
    public function specPostInsert()
    {
        return $this->RefreshLdapCard();
    }

    /**
     * update groups table in USER database
     *
     * @param int  $docid
     * @param bool $multiple
     *
     * @return string error message
     * @throws Exception
     */
    public function postInsertDocument($docid, $multiple = false)
    {
        $err = "";
        if ($multiple == false) {
            $gid = $this->getRawValue("US_WHATID");
            if ($gid > 0) {
                /**
                 * @var \SmartStructure\Iuser $du
                 */
                $du = DocManager::getDocument($docid);
                if ($du) {
                    $uid = $du->getRawValue("us_whatid");
                    if ($uid > 0) {
                        $g = new \Group("", $uid);
                        $g->iduser = $uid;
                        $g->idgroup = $gid;
                        $err = $g->Add();
                        if ($err == "OK") {
                            $err = "";
                        }
                        if ($err == "") {
                            $du->disableEditControl();
                            $du->RefreshDocUser(); // to refresh group of user attributes
                            $du->enableEditControl();
                            $this->RefreshGroup();
                        }
                    }
                }
            }
        }
        return $err;
    }

    /**
     * update groups table in USER database
     *
     * @param array $tdocid
     *
     * @return string error message
     * @throws Exception
     */
    public function postInsertMultipleDocuments($tdocid)
    {
        $err = "";
        
        $gid = $this->getRawValue("US_WHATID");
        if ($gid > 0) {
            $g = new \Group("");
            foreach ($tdocid as $k => $docid) {
                /**
                 * @var \SmartStructure\Iuser $du
                 */
                $du = DocManager::getDocument($docid);
                if ($du) {
                    $uid = $du->getRawValue("us_whatid");
                    if ($uid > 0) {
                        $g->iduser = $uid;
                        $g->idgroup = $gid;
                        $err = $g->Add();
                        if ($err == "") {
                            $du->disableEditControl();
                            $du->RefreshDocUser();
                            $du->enableEditControl();
                        }
                    }
                }
            }
            
            $this->RefreshGroup();
        }
        return $err;
    }

    /**
     * update groups table in USER database before suppress
     *
     * @param int  $docid
     * @param bool $multiple
     *
     * @return string error message
     * @throws Exception
     */
    public function postRemoveDocument($docid, $multiple = false)
    {
        $err = "";
        $gid = $this->getRawValue("US_WHATID");
        if ($gid > 0) {
            /**
             * @var \SmartStructure\Iuser $du
             */
            $du = DocManager::getDocument($docid);
            if ($du) {
                $uid = $du->getRawValue("us_whatid");
                if ($uid > 0) {
                    $g = new \Group("", $gid);
                    $g->iduser = $gid;
                    $err = $g->SuppressUser($uid);
                    if ($err == "") {
                        $du->disableEditControl();
                        $du->RefreshDocUser();
                        $du->enableEditControl();
                        $this->RefreshGroup();
                    }
                }
            }
        }
        return $err;
    }
    public function postDelete()
    {
        $gAccount = $this->getAccount();
        if ($gAccount) {
            $gAccount->Delete();
        }
    }
    /**
     * (re)insert members of the group in folder from USER databasee
     *
     * @return string error message, if no error empty string
     */
    public function insertGroups()
    {
        $gAccount = $this->getAccount();
        $err = "";
        // get members
        $tu = $gAccount->GetUsersGroupList($gAccount->id);
        
        if (is_array($tu)) {
            parent::Clear();
            $tfid = array();
            foreach ($tu as $k => $v) {
                //	if ($v["fid"]>0)  $err.=$this->AddFile($v["fid"]);
                if ($v["fid"] > 0) {
                    $tfid[] = $v["fid"];
                }
            }
            $err = $this->QuickInsertMSDocId($tfid); // without postInsert
            $this->specPostInsert();
        }
        return $err;
    }
    /**
     * insert members in a group in folder
     * it does not modify anakeen database (use only when anakeen database if updated)
     * must be use after a group add in anakeen database (use only for optimization in ::setGroups
     *
     * @param int $docid user doc parameter
     * @return string error message, if no error empty string
     */
    public function insertMember($docid)
    {
        $err = $this->insertDocument($docid, "latest", true); // without postInsert
        $this->setValue("grp_isrefreshed", "0");
        $this->modify(true, array(
            "grp_isrefreshed"
        ), true);
        
        return $err;
    }
    /**
     * suppress members of the group in folder
     * it does not modify anakeen database (use only when anakeen database if updated)
     * must be use after a group add in anakeen database (use only for optimization in ::setGroups
     *
     * @param int $docid user doc parameter
     * @return string error message, if no error empty string
     */
    public function deleteMember($docid)
    {
        $err = $this->removeDocument($docid, true); // without postInsert
        $this->setValue("grp_isrefreshed", "0");
        $this->modify(true, array(
            "grp_isrefreshed"
        ), true);
        
        return $err;
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
                $this->setValue("US_WHATID", $wuser->id);
                $this->setValue("GRP_NAME", $wuser->lastname);
                //   $this->setValue("US_FNAME",$wuser->firstname);
                $this->setValue("US_LOGIN", $wuser->login);
                
                $this->setValue("US_MEID", $this->id);
                // search group of the group
                $g = new \Group("", $wid);
                $tglogin = $tgid = array();
                if (count($g->groups) > 0) {
                    foreach ($g->groups as $gid) {
                        $gt = new \Anakeen\Core\Account("", $gid);
                        $tgid[$gid] = $gt->fid;
                        $tglogin[$gid] = $this->getTitle($gt->fid);
                    }
                    $this->setValue("GRP_IDPGROUP", $tgid);
                } else {
                    $this->setValue("GRP_IDPGROUP", " ");
                }
                $this->setValue("grp_roles", $wuser->getRoles(false));
                $err = $this->modify(true, array(
                    "us_whatid",
                    "grp_name",
                    "grp_roles",
                    "us_login",
                    "us_meid",
                    "grp_idgroup"
                ));
            } else {
                $err = sprintf(_("group %d does not exist"), $wid);
            }
        }
        return $err;
    }
    /**
     * refresh members of the group from USER database
     */
    public function refreshMembers()
    {
        $err = '';
        
        $wid = $this->getRawValue("us_whatid");
        if ($wid > 0) {
            $u = $this->getAccount(true);
            
            $tu = $u->GetUsersGroupList($wid, true);
            $tglogin = '';
            if (count($tu) > 0) {
                foreach ($tu as $uid => $tvu) {
                    if ($tvu["accounttype"] == \Anakeen\Core\Account::GROUP_TYPE) {
                        $tgid[$uid] = $tvu["fid"];
                        //	  $tglogin[$uid]=$this->getTitle($tvu["fid"]);
                        $tglogin[$tvu["fid"]] = $tvu["lastname"];
                    }
                }
            }
            
            if (is_array($tglogin)) {
                uasort($tglogin, "strcasecmp");
                $this->setValue("GRP_IDGROUP", array_keys($tglogin));
            } else {
                $this->clearValue("GRP_IDGROUP");
            }
            
            $err = $this->modify();
        }
        return $err;
    }
    /**
     * Flush/empty group's content
     */
    public function clear()
    {
        $err = '';
        $content = $this->getContent(false);
        if (is_array($content)) {
            foreach ($content as $tdoc) {
                $err.= $this->removeDocument($tdoc['id']);
            }
        }
        return $err;
    }
}
