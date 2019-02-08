<?php
/**
 * User Group Definition
 *
 */

class Group extends \Anakeen\Core\Internal\DbObj
{
    public $fields = ["iduser", "idgroup"];

    public $id_fields = ["iduser"];

    public $dbtable = "groups";

    public $sqlcreate
        = "
create table groups ( iduser      int not null,
                      idgroup    int not null);
create index groups_idx1 on groups(iduser);
create unique index groups_idx2 on groups(iduser,idgroup);
create trigger t_nogrouploop before insert or update on groups for each row execute procedure nogrouploop();";

    public $groups = array(); // user groups
    public $iduser;
    public $idgroup;

    protected $syncAccount = true;

    private $allgroups;
    private $levgid;

    /**
     * get groups of a user
     * set groups attribute. This attribute containt id of group of a user
     *
     * @return bool true if at least one group
     */
    public function getGroups()
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);

        $query->AddQuery("iduser='{$this->iduser}'");

        $str = <<< 'SQL'
SELECT groups.idgroup as gid 
from groups, users 
where groups.idgroup=users.id and users.accounttype!='R' and groups.iduser=%d order by accounttype, lastname;
SQL;
        $sql = sprintf($str, $this->iduser);

        \Anakeen\Core\DbManager::query($sql, $groupIds, true, false);
        $this->groups = $groupIds;

        return (count($groupIds) > 0);
    }

    /**
     * suppress a user from the group
     *
     * @param int  $uid    user identifier to suppress
     * @param bool $nopost set to to true to not perform postDelete methods
     *
     * @return string error message
     */
    public function suppressUser($uid, $nopost = false)
    {
        $err = "";

        if (($this->iduser > 0) && ($uid > 0)) {
            $err = $this->query("delete from groups where idgroup=" . $this->iduser . " and iduser=$uid");
            $err .= $this->query("delete from sessions where userid=$uid");

            $dbf = $this->dbaccess;
            $g = new Group($dbf);
            $err .= $g->query("delete from groups where idgroup=" . $this->iduser . " and iduser=$uid");

            if (!$nopost) {
                $this->PostDelete($uid);
            }
        }
        return $err;
    }

    /**
     * initialise groups for a user
     *
     * @param $id
     */
    public function postSelect($id)
    {
        $this->getGroups();
    }

    public function preInsert()
    {
        // verify is exists
        $err = $this->query(sprintf("select * from groups where idgroup=%s and iduser=%s", $this->idgroup, $this->iduser));
        if ($this->numrows() > 0) {
            $err = "OK"; // just to say it is not a real error
        }
        return $err;
    }

    public function postDelete($uid = 0)
    {
        if ($uid) {
            $u = new \Anakeen\Core\Account("", $uid);
        } else {
            $u = new \Anakeen\Core\Account("", $this->iduser);
        }
        $u->updateMemberOf();
        if ($u->accounttype != \Anakeen\Core\Account::USER_TYPE) {
            // recompute all doc profil
            $this->resetAccountMemberOf();
        } else {
            $dbf = $this->dbaccess;
            $g = new Group($dbf);
            $g->iduser = $this->iduser;
            $g->idgroup = $this->idgroup;
            $err = $g->query("delete from groups where idgroup=" . $this->iduser . " and iduser=" . $u->id);
            if ($err == "") {
                // if it is a user (not a group)
                $g->query("delete from permission where computed");

                $p = new Permission($this->dbaccess);
                $p->deletePermission($g->iduser, null, true);
            }
        }
    }

    public function postInsert()
    {
        $err = $this->query(sprintf("delete from sessions where userid=%d", $this->iduser));
        //    $this->FreedomCopyGroup();
        $u = new \Anakeen\Core\Account("", $this->iduser);

        $u->updateMemberOf();

        if ($u->accounttype != \Anakeen\Core\Account::USER_TYPE) {
            // recompute all doc profil
            $this->resetAccountMemberOf();
        } else {
            $dbf = $this->dbaccess;
            $g = new Group($dbf);
            $g->iduser = $this->iduser;
            $g->idgroup = $this->idgroup;
            $err = $g->add(true);
            if ($err == "" || $err == "OK") {
                // if it is a user (not a group)
                $g->query("delete from permission where computed");

                $p = new Permission($this->dbaccess);
                $p->deletePermission($g->iduser, null, true);
                $err = "";
            }
        }

        return $err;
    }

    /**
     * @param boolean $syncAccount
     */
    public function setSyncAccount($syncAccount)
    {
        $this->syncAccount = $syncAccount;
    }

    /**
     * recompute all memberof properties of user accounts
     */
    public function resetAccountMemberOf()
    {
        if ($this->syncAccount) {
            $this->query(sprintf("delete from sessions where userid=%d", $this->iduser));
            $this->query("delete from permission where computed");

            \Anakeen\Core\DbManager::query("select * from users order by id", $tusers);
            $u = new \Anakeen\Core\Account($this->dbaccess);
            foreach ($tusers as $tu) {
                $u->affect($tu);
                $u->updateMemberOf();
            }
        }
    }

    /**
     * get ascendant direct group and group of group
     */
    public function getAllGroups()
    {
        $allg = $this->groups;
        foreach ($this->groups as $k => $gid) {
            $og = new Group($this->dbaccess, $gid);
            $allg = array_merge($allg, $og->getAllGroups());
        }
        $allg = array_unique($allg);

        return $allg;
    }

    /**
     * get all child (descendant) group of this group
     *
     * @param $pgid
     *
     * @return array id
     */
    public function getChildsGroupId($pgid)
    {
        $this->_initAllGroup();

        $groupsid = array();

        if ($this->allgroups) {
            foreach ($this->allgroups as $k => $v) {
                if ($v["idgroup"] == $pgid) {
                    $uid = $v["iduser"];
                    $groupsid[$uid] = $uid;
                    //	  $groupsid=array_merge($groupsid, $this->getChildsGroup($v["iduser"]));
                    $groupsid += $this->getChildsGroupId($uid);
                }
            }
        }
        return $groupsid;
    }

    /**
     * get all parent (ascendant) group of this group
     *
     * @param     $pgid
     * @param int $level
     *
     * @return array id
     */
    public function getParentsGroupId($pgid, $level = 0)
    {
        $this->_initAllGroup();

        $groupsid = array();

        if ($this->allgroups) {
            foreach ($this->allgroups as $k => $v) {
                if ($v["iduser"] == $pgid) {
                    $gid = $v["idgroup"];
                    $groupsid[$gid] = $gid;
                    if (isset($this->levgid[$gid])) {
                        $this->levgid[$gid] = max($level, $this->levgid[$gid]);
                    } else {
                        $this->levgid[$gid] = $level;
                    }

                    $groupsid += $this->getParentsGroupId($gid, $level + 1);
                }
            }
        }
        return $groupsid;
    }

    /**
     * get all parent (ascendant) group of this group
     *
     * @param $pgid
     * @param $uasid
     *
     * @return array id
     */
    public function getDirectParentsGroupId($pgid, &$uasid)
    {
        $this->levgid = array();
        $this->getParentsGroupId($pgid);
        //print_r2($this->levgid);
        $groupsid = array();
        asort($this->levgid);
        foreach ($this->levgid as $k => $v) {
            if ($v == 0) {
                $groupsid[$k] = $k;
            } else {
                $uasid[$k] = $k;
            }
        }
        return $groupsid;
    }

    private function _initAllGroup()
    {
        if (!isset($this->allgroups)) {
            $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, self::class);
            $list = $query->Query(0, 0, "TABLE", "select * from groups where iduser in (select id from users where accounttype='G')");
            if ($list) {
                foreach ($list as $v) {
                    $this->allgroups[] = $v;
                }
            }
        }
    }
}
