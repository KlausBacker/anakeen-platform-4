<?php

namespace Anakeen\Core;

use Anakeen\Core\Utils\Strings;
use Anakeen\LogManager;
use Anakeen\SmartStructures\Igroup\IgroupLib;

define("GALL_ID", 2);
define("ANONYMOUS_ID", 3);
define("GADMIN_ID", 4);

/**
 * Manage User, Group and Role account object
 *
 * @class Account
 */
class Account extends Internal\DbObj
{
    const ANONYMOUS_ID = 3;
    const GALL_ID = 2;
    const GADMIN_ID = 4;
    const ADMIN_ID = 1;

    const USER_TYPE = "U";
    const GROUP_TYPE = "G";
    const ROLE_TYPE = "R";

    public $fields
        = array(
            "id",
            "lastname",
            "firstname",
            "login",
            "password",
            "substitute",
            "accounttype",
            "memberof",
            "expires",
            "passdelay",
            "status",
            "mail",
            "fid"
        );

    /**
     * @var string numeric id
     */
    public $id;
    public $lastname;
    public $firstname;
    public $login;
    public $password;

    public $expires;
    public $passdelay;
    public $status;
    public $mail;
    public $fid;
    public $memberof;
    /**
     * @var string U|G|R
     */
    public $accounttype;
    /**
     * @var int the substitute account identifier
     */
    public $substitute;
    /**
     * family identifier of user document default is IUSER/IGROUP
     *
     * @var string
     */
    public $famid;
    /**
     * @var string new password
     */
    public $password_new;
    public $id_fields
        = array(
            "id"
        );

    public $dbtable = "users";

    public $order_by = "lastname, accounttype";

    public $fulltextfields
        = array(
            "login",
            "lastname",
            "firstname"
        );

    public $sqlcreate
        = "
create table users ( id      int not null,
                primary key (id),
                        lastname   text,
                        firstname  text,
                        login      text not null,
                        password   text,
                        substitute      int,
                        accounttype char,
                        memberof   int[],
                        expires    int,
                        passdelay  int,
                        status     char,
                        mail       text,
                        fid int);
create index users_idx2 on users(lastname);
create index users_idx3 on users(accounttype);
create index users_idx4 on users(substitute);
CREATE UNIQUE INDEX users_login on users (login);
create sequence seq_id_users start 10;";


    /**
     * affect account from login name
     *
     * @param string $login login
     *
     * @return bool true if ok
     */
    public function setLoginName($login)
    {
        $login = Strings::mbTrim(mb_strtolower($login));

        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->AddQuery("login='" . pg_escape_string($login) . "'");

        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $this->Affect($list[0]);
            return true;
        }
        return false;
    }

    /**
     * return substitute account
     * return null if no susbtitute
     *
     * @return Account|null
     */
    public function getSubstitute()
    {
        if ($this->isAffected()) {
            if ($this->substitute) {
                return new Account($this->dbaccess, $this->substitute);
            }
        }
        return null;
    }

    /**
     * return incumbent ids account list (accounts which has this account as substitute)
     *
     * @param bool $returnSystemIds set to true to return system account id, false to return document user id
     *
     * @return int[]
     */
    public function getIncumbents($returnSystemIds = true)
    {
        $incumbents = array();
        if ($this->isAffected()) {
            $sql = sprintf("select %s from users where substitute=%d;", $returnSystemIds ? 'id' : 'fid', $this->id);
            DbManager::query($sql, $incumbents, true, false);
        }
        return $incumbents;
    }

    /**
     * set substitute to this user
     * this user become the incumbent of $substitute
     *
     * @param string $substitute login or user system id
     *
     * @return string error message (empty if not)
     */
    public function setSubstitute($substitute)
    {
        $err = '';
        if (!$this->isAffected()) {
            $err = sprintf(_("cannot set substitute account object not affected"));
        }
        if ($err) {
            return $err;
        }
        if (!$substitute && !$this->substitute) {
            // Nothing to do
            return "";
        }
        if ($substitute) {
            if (!(is_numeric($substitute))) {
                $sql = sprintf("select id from users where login = '%s'", pg_escape_string($substitute));
                DbManager::query($sql, $substituteId, true, true);
                if ($substituteId) {
                    $substitute = $substituteId;
                } else {
                    $err = sprintf(_("cannot set substitute %s login not found"), $substitute);
                }
            }
            if ($err) {
                return $err;
            }
            $sql = sprintf("select id from users where id = '%d'", $substitute);
            DbManager::query($sql, $substituteId, true, true);
            if (!$substituteId) {
                $err = sprintf(_("cannot set substitute %s id not found"), $substitute);
            }
        }
        if ($err) {
            return $err;
        }
        if ($substitute == $this->id) {
            $err = sprintf(_("cannot substitute itself"));
        }
        if ($err) {
            return $err;
        }
        $oldSubstitute = $this->substitute;
        $this->substitute = $substitute;

        $err = $this->modify();
        if (!$err) {
            $u = new \Anakeen\Core\Account($this->dbaccess, $this->substitute);
            $u->updateMemberOf();
            if ($oldSubstitute) {
                $u->select($oldSubstitute);
                $u->updateMemberOf();
            }


            $cu = ContextManager::getCurrentUser();
            if ($cu->id == $u->id) {
                $cu->revert();
            }
        }
        return $err;
    }

    /**
     * affect account from its document id
     *
     * @param int $fid
     *
     * @return bool true if ok
     */
    public function setFid($fid)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->AddQuery(sprintf("fid = %d", $fid));
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb != 0) {
            $this->Affect($list[0]);
        } else {
            return false;
        }
        return true;
    }

    public function preInsert()
    {
        $err = '';
        if ((!$this->login) && $this->accounttype == self::ROLE_TYPE) {
            // compute auto role reference
            $this->login = uniqid('role');
        }

        if ($this->setloginName($this->login)) {
            return _("this login exists");
        }
        if ($this->login == "") {
            return _("login must not be empty");
        }
        $this->login = mb_strtolower($this->login);
        if ($this->id == "") {
            $res = pg_query($this->dbid, "select nextval ('seq_id_users')");
            $arr = pg_fetch_array($res, 0);
            $this->id = $arr["nextval"];
        }

        if (($this->accounttype == self::GROUP_TYPE) || ($this->accounttype == self::ROLE_TYPE)) {
            $this->password = '-'; // no passwd for group,role
        }
        if (!$this->accounttype) {
            $this->accounttype = self::USER_TYPE;
        }

        if ($this->accounttype === self::USER_TYPE && !$this->status) {
            $this->status = "A";
        }
        if (!$this->lastname && !$this->firstname) {
            $this->lastname = $this->login;
        }
        if (!$this->memberof) {
            $this->memberof='{}';
        }

        if (isset($this->password_new) && ($this->password_new != "")) {
            $this->computepass($this->password_new, $this->password);
        }
        //expires and passdelay
        $this->GetExpires();
        return $err;
    }

    public function postInsert()
    {
        return $this->synchroAccountDocument();
    }

    public function postUpdate()
    {
        return $this->synchroAccountDocument();
    }

    public function preUpdate()
    {
        if (isset($this->password_new) && ($this->password_new != "")) {
            $this->computepass($this->password_new, $this->password);
        }

        $this->login = mb_strtolower($this->login);
        //expires and passdelay
        $this->GetExpires();
    }

    public function postDelete()
    {
        $err = '';
        $group = new \Group($this->dbaccess, $this->id);
        $ugroups = $group->groups;
        // delete reference in group table
        $sql = sprintf("delete from groups where iduser=%d or idgroup=%d", $this->id, $this->id);
        DbManager::query($sql);

        IgroupLib::refreshGroups($ugroups, true);

        ContextManager::getSession()->closeUsers($this->id);

        return $err;
    }


    /**
     * return display name of a user
     *
     * @param int $uid user identifier
     *
     * @return string|null firstname and lastname or null if not found
     */
    public static function getDisplayName($uid)
    {
        static $tdn = array();

        $uid = intval($uid);
        if ($uid > 0) {
            if (isset($tdn[$uid])) {
                return $tdn[$uid];
            }
            $dbid = \Anakeen\Core\DbManager::getDbId();
            $res = pg_query($dbid, "select firstname, lastname  from users where id=$uid");
            if (pg_num_rows($res) > 0) {
                $arr = pg_fetch_array($res, 0);
                if ($arr["firstname"]) {
                    $tdn[$uid] = $arr["firstname"] . ' ' . $arr["lastname"];
                } else {
                    $tdn[$uid] = $arr["lastname"];
                }
                return $tdn[$uid];
            }
            return null;
        }
        return null;
    }

    /**
     * Same as ::getDisplayName but for current object
     */
    public function getAccountName()
    {
        return trim(sprintf("%s %s", $this->firstname, $this->lastname));
    }

    /**
     * return system user identifier from user document reference
     *
     * @static
     * @param int $fid
     *
     * @return int
     * @see getFidFromUid
     *
     */
    public static function getUidFromFid($fid)
    {
        $uid = 0;
        if ($fid) {
            DbManager::query(sprintf("select id from users where fid=%d", $fid), $uid, true, true);
        }
        return $uid;
    }

    /**
     * return user document reference from system user identifier
     *
     * @static
     * @param int $uid
     *
     * @return int
     * @see getUidFromFid
     *
     */
    public static function getFidFromUid($uid)
    {
        $fid = 0;
        if ($uid) {
            DbManager::query(sprintf("select fid from users where id=%d", $uid), $fid, true, true);
        }
        return $fid;
    }


    /**
     * update user from IUSER document
     *
     * @param int $fid document id
     * @param string $lname last name
     * @param string $fname first name
     * @param string $expires expiration date
     * @param int $passdelay password delay
     * @param string $login login
     * @param string $status 'A' (Activate) , 'D' (Desactivated)
     * @param string $pwd1 password one
     * @param string $pwd2 password two
     * @param string $extmail mail address
     * @param array $roles
     * @param int $substitute system substitute id
     *
     * @return string error message
     */
    public function updateUser(
        $fid,
        $lname,
        $fname,
        $expires,
        $passdelay,
        $login,
        $status,
        $pwd1,
        $pwd2,
        $extmail = '',
        array $roles
        = array(
            -1
        ),
        $substitute = -1
    ) {
        $this->lastname = $lname;
        $this->firstname = $fname;
        $this->status = $status;
        if ($login != "") {
            $this->login = $login;
        }
        //don't modify password in database even if force constraint
        if ($pwd1 == $pwd2 and $pwd1 <> "") {
            $this->password_new = $pwd2;
        }

        if ($extmail != "") {
            $this->mail = trim($extmail);
        }
        if ($expires > 0) {
            $this->expires = $expires;
        }
        if ($passdelay > 0) {
            $this->passdelay = $passdelay;
        } elseif ($passdelay == -1) { // suppress expire date
            $this->expires = 0;
            $this->passdelay = 0;
        }

        $this->fid = $fid;
        if (!$this->isAffected()) {
            $err = $this->add(true);
        } else {
            $err = $this->modify(true);
        }
        if ($roles != array(-1)) {
            $err .= $this->setRoles($roles);
        } else {
            $this->updateMemberOf();
        }

        if ((!$err) && ($substitute > -1)) {
            $err = $this->setSubstitute($substitute);
        }
        if (!$err) {
            $this->synchroAccountDocument();
        }
        return $err;
    }

    /**
     * update user from FREEDOM IGROUP document
     *
     * @param int $fid document id
     * @param string $gname group name
     * @param string $login login
     * @param array $roles system role ids
     * @return string error message
     */
    public function setGroups(
        $fid,
        $gname,
        $login,
        array $roles
        = array(
            -1
        )
    ) {
        $err = "";
        if ($gname != "") {
            $this->lastname = $gname;
        }
        if (($this->login == "") && ($login != "")) {
            $this->login = $login;
        }
        $isNewUser = !$this->isAffected();

        $this->fid = $fid;

        if (!$err) {
            if ($isNewUser) {
                $this->accounttype = self::GROUP_TYPE;
                $err = $this->add(true);
            } else {
                $err = $this->Modify(true);
            }
        }

        if ($roles != array(-1)) {
            $err = $this->setRoles($roles);
        } else {
            $this->updateMemberOf();
        }

        if (!$err) {
            $this->synchroAccountDocument();
        }
        return $err;
    }

    /**
     * revert values from database
     */
    public function revert()
    {
        if ($this->isAffected()) {
            $this->select($this->id);
        }
    }
    //Add and Update expires and passdelay for password
    //Call in PreUpdate and PreInsert
    public function getExpires()
    {
        if (intval($this->passdelay) == 0) {
            // neither expire
            $this->expires = "0";
            $this->passdelay = "0";
        } elseif (intval($this->expires) == 0) {
            $this->expires = time() + $this->passdelay;
        }
    }

    public function synchroAccountDocument()
    {
        $dbaccess = $this->dbaccess;
        if ($dbaccess == "") {
            return _("no freedom DB access");
        }
        if ($this->fid <> "") {
            /**
             * @var \SmartStructure\IUSER $iuser
             */
            $iuser = \Anakeen\Core\SEManager::getDocument($this->fid);

            //Update from what
            $err = $iuser->RefreshDocUser();
        } else {
            if ($this->famid != "") {
                $fam = $this->famid;
            } elseif ($this->accounttype == self::GROUP_TYPE) {
                $fam = "IGROUP";
            } elseif ($this->accounttype == self::ROLE_TYPE) {
                $fam = "ROLE";
            } else {
                $fam = "IUSER";
            }
            $filter = array(
                "us_whatid = '" . $this->id . "'"
            );
            $tdoc = \Anakeen\SmartStructures\Dir\DirLib::internalGetDocCollection(
                $dbaccess,
                0,
                0,
                "ALL",
                $filter,
                1,
                "LIST",
                $fam
            );
            if (count($tdoc) == 0) {
                //Create a new doc IUSER

                /**
                 * @var \SmartStructure\IUSER $iuser
                 */
                $iuser = SEManager::createDocument($fam);
                $iuser->SetValue("US_WHATID", $this->id);
                $iuser->add();
                $this->fid = $iuser->id;
                $this->modify(true, array(
                    'fid'
                ), true);
                $err = $iuser->refreshDocUser();
            } else {
                /**
                 * @var \SmartStructure\IUSER $iuser
                 */
                $iuser = $tdoc[0];
                $this->fid = $iuser->id;
                $this->modify(true, array(
                    'fid'
                ), true);
                $err = $iuser->refreshDocUser();
            }
        }
        return $err;
    }

    // --------------------------------------------------------------------
    public function computepass($pass, &$passk)
    {
        $salt = '';
        $salt_space = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./";
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < 16; $i++) {
            $salt .= $salt_space[rand(0, strlen($salt_space) - 1)];
        }
        $passk = crypt($pass, "\$5\${$salt}");
        $this->_deleteUserSessions();
    }

    /**
     * @param string $pass clear password to test
     *
     * @return bool
     */
    public function checkpassword($pass)
    {
        if ($this->accounttype !== 'U') {
            return false;
        } // don't log in group or role
        return ($this->checkpass($pass, $this->password));
    }

    // --------------------------------------------------------------------
    public function checkpass($pass, $passk)
    {
        if (substr($passk, 0, 3) !== '$5$') {
            /* Old DES crypted passwords => SSHA256 crypting*/
            $salt = substr($passk, 0, 2);
            $passres = crypt($pass, $salt);
            if ($passres == $passk) {
                $this->computepass($pass, $this->password);
                $err = $this->modify(true, array(
                    'password'
                ), true);
                if ($err == '') {
                    LogManager::info(sprintf(
                        'User "%s" password crypted with salted SHA256 algorithm.',
                        $this->login
                    ));
                }
            }
        } else {
            $salt = substr($passk, 3, 19);
            $passres = crypt($pass, "\$5\${$salt}");
        }
        return ($passres === $passk);
    }

    /**
     * return mail adress
     *
     * @param bool $rawmail set to false to have long mail with firstname and lastname
     *
     * @return string mail address empty if no mail
     */
    public function getMail($rawmail = true)
    {
        if ($this->accounttype === 'U') {
            if (!$this->mail) {
                return '';
            }
            if ($rawmail) {
                return $this->mail;
            } else {
                $dn = trim($this->firstname . ' ' . $this->lastname);
                return sprintf('"%s" <%s>', str_replace('"', '-', $dn), $this->mail);
            }
        } else {
            if (!$this->id) {
                return "";
            }
            if ($rawmail) {
                return IgroupLib::getMailGroup($this->id, true);
            } else {
                return IgroupLib::getMailGroup($this->id);
            }
        }
    }

    public function postInit()
    {
        $group = new \Group($this->dbaccess);

        $userAdmin = new Account($this->dbaccess);
        // Create admin user
        $userAdmin->id = Account::ADMIN_ID;
        $userAdmin->lastname = "Master";
        $userAdmin->firstname = "Anakeen Platform";
        $userAdmin->password_new = "anakeen";
        $userAdmin->login = "admin";
        $userAdmin->add(true);

        $group->iduser = $userAdmin->id;

        // Create default group

        $groupAll = new Account($this->dbaccess);
        $groupAll->id = Account::GALL_ID;
        $groupAll->lastname = "Main Group";
        $groupAll->firstname = "";
        $groupAll->login = "all";
        $groupAll->accounttype = self::GROUP_TYPE;
        $groupAll->add(true);

        $group->idgroup = $groupAll->id;
        $group->add(true);

        // Create anonymous user

        $anonymousUser = new Account($this->dbaccess);
        $anonymousUser->id = Account::ANONYMOUS_ID;
        $anonymousUser->lastname = "Anonymous";
        $anonymousUser->firstname = "Guest";
        $anonymousUser->login = "anonymous";
        $anonymousUser->password = "-";
        $anonymousUser->accounttype = self::USER_TYPE;
        $anonymousUser->add(true);

        // Create admin group

        $groupAdmin = new Account($this->dbaccess);
        $groupAdmin->id = Account::GADMIN_ID;
        $groupAdmin->lastname = "Administrators";
        $groupAdmin->firstname = "";
        $groupAdmin->login = "gadmin";
        $groupAdmin->accounttype = self::GROUP_TYPE;
        $groupAdmin->add(true);

        // Store error messages
    }

    /**
     * get the first incumbent which has $acl privilege
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc document to verify
     * @param string $acl document acl name
     *
     * @return string incumbent's name which has privilege
     */
    public function getIncumbentPrivilege(\Anakeen\Core\Internal\SmartElement &$doc, $acl)
    {
        if ($this->id == 1) {
            return '';
        }
        if ($incumbents = $this->getIncumbents()) {
            if ($doc->control($acl, true) != '') {
                foreach ($incumbents as $aIncumbent) {
                    $eErr = $doc->accessControl()->controlUserId($doc->profid, $aIncumbent, $acl);
                    if (!$eErr) {
                        return Account::getDisplayName($aIncumbent);
                    }
                }
            }
        }
        return '';
    }

    /**
     * get All Users (not group not role)
     *
     * @static
     *
     * @param string $qtype return type LIST|TABLE|ITEM
     * @param int $start
     * @param int $slice
     * @param string $filteruser keyword to filter user on login or lastname
     *
     * @return array
     */
    public static function getUserList($qtype = "LIST", $start = 0, $slice = 0, $filteruser = '')
    {
        $query = new \Anakeen\Core\Internal\QueryDb("", self::class);
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='U')");
        if ($filteruser) {
            $query->AddQuery("(login ~* '" . pg_escape_string($filteruser) . "')" . " or " . "(lastname ~* '"
                . pg_escape_string($filteruser) . "')");
        }
        return ($query->Query($start, $slice, $qtype));
    }

    /**
     * get All groups
     *
     * @param string $qtype return type LIST|TABLE|ITEM
     *
     * @return array
     */
    public static function getGroupList($qtype = "LIST")
    {
        $query = new \Anakeen\Core\Internal\QueryDb("", self::class);
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='G')");
        $l = $query->Query(0, 0, $qtype);
        return ($query->nb > 0) ? $l : array();
    }

    /**
     * get All Roles
     *
     * @param string $qtype return type LIST|TABLE|ITEM
     *
     * @return array
     */
    public static function getRoleList($qtype = "LIST")
    {
        $query = new \Anakeen\Core\Internal\QueryDb("", self::class);
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='R')");
        $l = $query->Query(0, 0, $qtype);
        return ($query->nb > 0) ? $l : array();
    }

    /**
     * get All users & groups (except role)
     *
     * @param string $qtype return type LIST|TABLE|ITEM
     *
     * @return array
     */
    public static function getUserAndGroupList($qtype = "LIST")
    {
        $query = new \Anakeen\Core\Internal\QueryDb("", self::class);
        $query->AddQuery("(accountType='G' or accountType='U')");

        $query->order_by = "accounttype, lastname";
        return ($query->Query(0, 0, $qtype));
    }

    /**
     * get All ascendant group ids of the user object
     */
    public function getGroupsId()
    {
        $sql = sprintf(
            "select idgroup from groups, users where groups.idgroup=users.id and users.accounttype='G' and groups.iduser=%d",
            $this->id
        );
        DbManager::query($sql, $groupsid, true, false);
        return $groupsid;
    }

    /**
     * for group :: get All user & groups ids in all descendant(recursive);
     *
     * @param int $id group identifier
     *
     * @param array $r
     * @return array of account array
     * @throws \Anakeen\Database\Exception
     */
    public function getRUsersList($id, $r = array())
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $list = $query->Query(
            0,
            0,
            "TABLE",
            "select users.* from users, groups where " . "groups.iduser=users.id and " . "idgroup=$id ;"
        );

        $uid = array();

        if ($query->nb > 0) {
            foreach ($list as $k => $v) {
                $uid[$v["id"]] = $v;
                if ($v["accounttype"] === "G") {
                    if (!in_array($v["id"], $r)) {
                        array_push($r, $v["id"]);
                        $uid += $this->GetRUsersList($v["id"], $r);
                    }
                }
            }
        }

        return $uid;
    }

    /**
     * for group :: get All direct user & groups ids
     *
     * @param int $gid group identifier
     * @param bool $onlygroup set to true if you want only child groups
     * @return array
     */
    public function getUsersGroupList($gid, $onlygroup = false)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $optgroup = '';
        if ($onlygroup) {
            $optgroup = " and users.accounttype='G' ";
        }

        $list = $query->Query(
            0,
            0,
            "TABLE",
            "select users.* from users, groups where " . "groups.iduser=users.id and " . "idgroup=$gid $optgroup;"
        );

        $uid = array();
        if ($query->nb > 0) {
            foreach ($list as $k => $v) {
                $uid[$v["id"]] = $v;
            }
        }

        return $uid;
    }

    /**
     * return all user members (recursive)
     *
     * @return array of user values ["login"=>, "id"=>, "fid"=>,...)
     */
    private function getUserMembers()
    {
        $g = new \Group($this->dbaccess);
        $lg = $g->getChildsGroupId($this->id);
        $lg[] = $this->id;
        $cond = \Anakeen\Core\DbManager::getSqlOrCond($lg, "idgroup", true);
        if (!$cond) {
            $cond = "true";
        }
        $condname = "";

        $sort = 'lastname';
        $sql = sprintf(
            "SELECT distinct on (%s, users.id) users.id, users.login, users.firstname , users.lastname, users.mail,users.fid " .
            "from users, groups where %s and (groups.iduser=users.id) %s and accounttype='U' order by %s",
            $sort,
            $cond,
            $condname,
            $sort
        );


        DbManager::query($sql, $result);

        return $result;
    }

    /**
     * return all group (recursive) /role of user
     *
     * @param string $accountFilter G|R to indicate if want only group or only role
     *
     * @return array of users characteristics
     */
    public function getUserParents($accountFilter = '')
    {
        $acond = '';
        if ($accountFilter) {
            $acond = sprintf("and users.accounttype='%s'", pg_escape_string($accountFilter));
        }
        $sql = sprintf("with recursive agroups(gid) as (
 select idgroup from groups,users where iduser = %d and users.id=groups.idgroup
union
 select idgroup from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.idgroup
) select users.* from agroups, users where users.id=agroups.gid %s order by lastname", $this->id, $acond);
        DbManager::query($sql, $parents);
        return $parents;
    }

    /**
     * get memberof for user without substitutes
     *
     * @param int $uid if not set it is the current account object else use another account identifier
     *
     * @return array
     * @throws \Anakeen\Exception
     */
    public function getStrictMemberOf($uid = -1)
    {
        if ($uid == -1) {
            $uid = $this->id;
        }
        if (!$uid) {
            return array();
        }
        // get all ascendants groupe,role of a user
        $sql = sprintf("with recursive agroups(gid, login, actype) as (
 select idgroup, users.login, users.accounttype from groups,users where iduser = %d and users.id=groups.idgroup
   union
 select idgroup, users.login, users.accounttype from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.idgroup
) select gid from agroups;", $uid);

        DbManager::query($sql, $gids, true, false);
        return $gids;
    }

    /**
     * update memberof fields with all group/role of user
     *
     * @param bool $updateSubstitute also update substitute by default
     *
     * @return array of memberof identificators
     * @throws \Anakeen\Exception
     */
    public function updateMemberOf($updateSubstitute = true)
    {
        if (!$this->id) {
            return array();
        }

        $lg = $this->getStrictMemberOf();
        // search incumbents
        $sql = sprintf("select id from users where substitute=%d;", $this->id);
        DbManager::query($sql, $incumbents, true, false);
        foreach ($incumbents as $aIncumbent) {
            $lg[] = $aIncumbent;
            // use strict no propagate substitutes
            $lg = array_merge($lg, $this->getStrictMemberOf($aIncumbent));
        }

        $lg = array_values(array_unique($lg));
        $this->memberof = '{' . implode(',', $lg) . '}';
        $err = $this->modify(true, array(
            'memberof'
        ), true);
        if ($err) {
            throw new \Anakeen\Exception($err);
        }
        if ($updateSubstitute && $this->substitute) {
            $u = new Account($this->dbaccess, $this->substitute);
            $u->updateMemberOf(false);
        }

        return $lg;
    }

    /**
     * return id of group/role id
     *
     * @param bool $useSystemId set to false to return document id instead of system id
     *
     * @return array
     */
    public function getMemberOf($useSystemId = true)
    {
        $memberOf = array();
        if (strlen($this->memberof) > 2) {
            $memberOf = explode(',', substr($this->memberof, 1, -1));
        }
        if (!$useSystemId) {
            if (!empty($memberOf)) {
                DbManager::query(sprintf(
                    "select fid from users where id in (%s)",
                    implode(',', $memberOf)
                ), $dUids, true);
                return $dUids;
            }
        }
        return $memberOf;
    }

    /**
     * return list of account (group/role) member for a user
     * return null if user not exists
     *
     * @static
     *
     * @param int $uid user identifier
     *
     * @param bool $strict if true no use delegation
     * @return array|null
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Exception
     */
    public static function getUserMemberOf($uid, $strict = false)
    {
        $cu = ContextManager::getCurrentUser();
        if ($cu->id == $uid) {
            if ($strict) {
                $memberOf = $cu->getStrictMemberOf();
            } else {
                $memberOf = $cu->getMemberOf();
            }
        } else {
            $u = new Account('', $uid);
            if ($u->isAffected()) {
                if ($strict) {
                    $memberOf = $u->getStrictMemberOf();
                } else {
                    $memberOf = $u->getMemberOf();
                }
            } else {
                return null;
            }
        }
        return $memberOf;
    }

    /**
     * verify if user is member of group (recursive)
     *
     * @param int $uid user identifier
     * @return bool
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public function isMember($uid)
    {
        $g = new \Group($this->dbaccess);
        $lg = $g->getChildsGroupId($this->id);
        $lg[] = $this->id;
        $cond = \Anakeen\Core\DbManager::getSqlOrCond($lg, "idgroup", true);
        if (!$cond) {
            $cond = "true";
        }

        $sql = sprintf(
            "select users.id from users, groups where %s and (groups.iduser=users.id) and users.id=%d and accounttype != 'G'",
            $cond,
            $uid
        );

        DbManager::query($sql, $result, true, true);

        return ($result != '');
    }

    /**
     * only use with group or role
     * if it is a group : get all direct user member of a group
     * if it is a role : het user which has role directly
     *
     * @param string $qtype LIST|TABLE|ITEM
     * @param bool $withgroup set to true to return sub group also
     * @param int|string $limit max users returned
     *
     * @return array of user properties
     */
    public function getGroupUserList($qtype = "LIST", $withgroup = false, $limit = "all")
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
        $query->order_by = "accounttype desc, lastname";
        $selgroup = "and (accounttype='U')";
        if ($withgroup) {
            $selgroup = "";
        }
        return $query->Query(
            0,
            $limit,
            $qtype,
            "select users.* from users, groups where " . "groups.iduser=users.id and "
            . "idgroup={$this->id} {$selgroup};"
        );
    }

    /**
     * get all users of a group/role direct or indirect
     *
     * @param int|string $limit max users returned
     * @param bool $onlyUsers set to true to have also sub groups
     *
     * @return array of user properties
     */
    public function getAllMembers($limit = "all", $onlyUsers = true)
    {
        if ($limit != 'all') {
            $limit = intval($limit);
        }
        if ($onlyUsers) {
            $sql = sprintf(
                "select * from users where memberof && '{%d}' and accounttype='U' order by lastname limit %s",
                $this->id,
                $limit
            );
        } else {
            $sql = sprintf(
                "select * from users where memberof && '{%d}' order by accounttype, lastname limit %s",
                $this->id,
                $limit
            );
        }
        DbManager::query($sql, $users);
        return $users;
    }

    /**
     * Get user token for open access
     *
     * @param bool|int $expireDelay set expiration delay in seconds (-1 if nether expire)
     * @param bool $oneshot set to true to use one token is consumed/deleted when used
     *
     * @param array $context get http var restriction
     * @param string $description text description information
     * @param bool $forceCreate set to true to always return a new token
     *
     * @return string
     * @throws \Anakeen\Exception
     */
    public function getUserToken(
        $expireDelay = -1,
        $oneshot = false,
        $context = array(),
        $description = "",
        $forceCreate = false
    ) {
        if ($expireDelay === -1 || $expireDelay === false) {
            $expireDelay = \UserToken::INFINITY;
        }
        if ($context && (count($context) > 0)) {
            $scontext = serialize($context);
        } else {
            $scontext = '';
        }

        if (!$this->isAffected()) {
            throw new \Anakeen\Exception(sprintf("User token : account must be affected"));
        }

        $expireDate = \UserToken::getExpirationDate($expireDelay);
        $tu = array();
        if (!$oneshot && !$forceCreate) {
            $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \UserToken::class);
            $q->addQuery(sprintf("userid=%d", $this->id));
            $q->addQuery(sprintf("expire='%s'", $expireDate));
            if ($scontext) {
                $q->addQuery("context='" . pg_escape_string($scontext) . "'");
            }
            $tu = $q->Query(0, 0, "TABLE");
            $create = ($q->nb == 0);
        } else {
            $create = true;
        }

        if ($create) {
            // create one
            $uk = new \UserToken("");
            $uk->userid = $this->id;
            $uk->description = $description;
            $uk->token = $uk->genToken();
            $uk->type = "CORE";
            $uk->expire = $uk->setExpiration($expireDelay);
            if ($uk->expire === false) {
                throw new \Anakeen\Exception(sprintf("User token : Invalid date. Expire must be a delay in seconds"));
            }
            $uk->expendable = $oneshot;
            $uk->context = $scontext;
            $err = $uk->add();
            if ($err) {
                throw new \Anakeen\Exception($err);
            }
            $token = $uk->token;
        } else {
            $token = $tu[0]["token"];
        }
        return $token;
    }


    /**
     * add a role to a user/group
     *
     * @param string $idRole system identicator or reference role (login)
     *
     * @return string error message
     */
    public function addRole($idRole)
    {
        if (!$this->isAffected()) {
            return \ErrorCode::getError("ACCT0002", $idRole);
        }
        if ($this->accounttype != self::USER_TYPE) {
            return \ErrorCode::getError("ACCT0003", $idRole, $this->login);
        }
        if (!is_numeric($idRole)) {
            DbManager::query(sprintf(
                "select id from users where login = '%s'",
                pg_escape_string($idRole)
            ), $idRoleW, true, true);
            if ($idRoleW) {
                $idRole = $idRoleW;
            }
        }
        if (!is_numeric($idRole)) {
            return \ErrorCode::getError("ACCT0001", $idRole, $this->login);
        }
        $g = new \Group($this->dbaccess);
        $g->idgroup = $idRole;
        $g->iduser = $this->id;
        $err = $g->add();
        if ($err == 'OK') {
            $err = '';
            $this->updateMemberOf();
        }
        return $err;
    }

    /**
     * set role set to a user/group
     *
     * @param array $roleIds system identicators or reference roles (login)
     *
     * @return string error message
     */
    public function setRoles(array $roleIds)
    {
        if (!$this->isAffected()) {
            return \ErrorCode::getError("ACCT0006", implode(',', $roleIds));
        }

        if ($this->accounttype == self::ROLE_TYPE) {
            return \ErrorCode::getError("ACCT0007", implode(',', $roleIds), $this->login);
        }
        $currentRoles=$this->getRoles();
        if (count($roleIds) === count($currentRoles)) {
            sort($roleIds);
            sort($currentRoles);
            if ($roleIds === $currentRoles) {
                // Nothing to do
                return "";
            }
        }
        $this->deleteRoles();
        $err = '';
        if ($this->accounttype == self::USER_TYPE || $this->accounttype == self::GROUP_TYPE) {
            $g = new \Group($this->dbaccess);
            foreach ($roleIds as $rid) {
                if (!is_numeric($rid)) {
                    DbManager::query(sprintf(
                        "select id from users where login = '%s'",
                        pg_escape_string($rid)
                    ), $idRoleW, true, true);
                    if ($idRoleW) {
                        $rid = $idRoleW;
                    }
                }
                if (!is_numeric($rid)) {
                    $err .= \ErrorCode::getError("ACCT0008", $rid, $this->login);
                } else {
                    $g->idgroup = $rid;
                    $g->iduser = $this->id;

                    $gerr = $g->add(true);
                    if ($gerr == 'OK') {
                        $gerr = '';
                    }
                    $err .= $gerr;
                }
            }

            $this->updateMemberOf();
        }
        if ($this->accounttype == self::GROUP_TYPE) {
            // must propagate to users
            $lu = $this->getUserMembers();
            $uw = new Account($this->dbaccess);
            foreach ($lu as $u) {
                $uw->id = $u["id"];
                $uw->updateMemberOf();
            }
        }
        return $err;
    }

    /**
     * return direct role ids (not role which can comes from parent groups)
     *
     * @param bool $useSystemId if true return system id else return document ids
     *
     * @return array
     */
    public function getRoles($useSystemId = true)
    {
        $returnColumn = $useSystemId ? "id" : "fid";
        $sql = sprintf(
            "SELECT users.%s from users, groups where groups.iduser=%d and users.id = groups.idgroup and users.accounttype='R'",
            $returnColumn,
            $this->id
        );
        DbManager::query($sql, $rids, true, false);
        return $rids;
    }

    /**
     * return direct and indirect role which comes from groups
     *
     * @return array of users properties
     */
    public function getAllRoles()
    {
        $mo = $this->getMemberOf();
        if (empty($mo)) {
            return array();
        }
        $sql = sprintf("SELECT * from users where id in (%s) and accounttype='R'", implode(',', $mo));

        DbManager::query($sql, $rusers);
        return $rusers;
    }

    /**
     * delete all role of a user/group
     *
     * @return string error message
     */
    public function deleteRoles()
    {
        if (!$this->isAffected()) {
            return \ErrorCode::getError("ACCT0004");
        }
        if ($this->accounttype == self::ROLE_TYPE) {
            return \ErrorCode::getError("ACCT0005", $this->login);
        }
        $sql = sprintf(
            "DELETE FROM groups USING users where groups.iduser=%d and users.id=groups.idgroup and users.accounttype='R'",
            $this->id
        );
        DbManager::query($sql);

        DbManager::query("delete from permission where computed");


        return "";
    }

    private function _deleteUserSessions()
    {
        if (\Anakeen\Router\AuthenticatorManager::$session !== null && \Anakeen\Router\AuthenticatorManager::$session->userid == $this->id) {
            \Anakeen\Router\AuthenticatorManager::$session->deleteUserSessionsExcept();
        } else {
            $session = new \Anakeen\Core\Internal\Session();
            $session->deleteUserSessionsExcept($this->id);
        }
    }
}
