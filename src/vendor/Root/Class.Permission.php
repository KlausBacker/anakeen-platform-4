<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Permission to execute actions
 *
 * @author     Anakeen
 * @version    $Id: Class.Permission.php,v 1.10 2006/06/01 12:54:05 eric Exp $
 * @package    FDL
 * @subpackage CORE
 */

/**
 */
class Permission extends \Anakeen\Core\Internal\DbObj
{
    public $fields = array(
        "id_user",
        "id_acl",
        "computed"
    );

    public $id_fields = array(
        "id_user",
    );

    public $dbtable = "permission";
    public $privileges = array(); // privileges array for a user (including group) in an application
    private $upprivileges = false; // specifific privileges array for a user in an application
    private $unprivileges = false; // specifific NO privileges array for a user in an application
    private $gprivileges = false; // privileges array for the group user
    public $sqlcreate = '
create table permission (id_user int not null,
                         id_acl int not null,
                         computed boolean default false);
create index permission_idx1 on permission(id_user);
create index permission_idx3 on permission(id_acl);
create index permission_idx4 on permission(computed);
                 ';

    public $id_user;
    public $id_acl;
    /**
     * @var bool
     */
    public $computed;

    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0, $computed = true)
    {
        if ($id && $id[0]) {
            parent::__construct($dbaccess, $id, $res, $dbid);
        } else {
            parent::__construct($dbaccess, '', $res, $dbid);
        }
        if (!$this->isAffected()) {
            if (is_array($id) && $id[0]) {
                $this->Affect(array(
                    "id_user" => $id[0],
                    "computed" => (!empty($id[2]))
                ));
                $this->GetPrivileges(false, $computed);
            }
        }
    }

    public function postSelect($id)
    {
        // init privileges
        $this->GetPrivileges();
    }

    public function postDelete()
    {
        // update privileges
        $this->GetPrivileges();
    }

    public function postUpdate()
    {
        // update privileges
        $this->GetPrivileges();
    }

    public function preInsert()
    {
        // no duplicate items
        if ($this->Exists($this->id_user, $this->id_acl)) {
            return "Permission ({$this->id_user},{$this->id_acl}) already exists...";
        }

        return "";
    }

    public function postInsert()
    {
        if (!$this->computed) {
            $this->query(sprintf("delete from permission where   abs(id_acl)=%d and computed", abs($this->id_acl)));
        }
        return "";
    }


    public function exists($userid, $aclid = 0)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Permission::class);
        $query->basic_elem->sup_where = array(
            "id_user='{$userid}'",
            "( computed = FALSE OR computed IS NULL )"
        );
        if ($aclid != 0) {
            $naclid = -$aclid;
            $query->AddQuery("(id_acl={$aclid}) OR (id_acl= {$naclid}) ");
        }
        $query->Query(0, 0, "TABLE");

        return ($query->nb > 0);
    }


    /**
     * return ACL up list for a user
     */
    public function getUpPrivileges()
    {
        if ($this->upprivileges === false) {
            $this->getPrivileges(true, false);
        }
        return $this->upprivileges;
    }

    /**
     * return ACL un list for a user
     */
    public function getUnPrivileges()
    {
        if ($this->unprivileges === false) {
            $this->GetPrivileges(true, false);
        }
        return $this->unprivileges;
    }

    /**
     * return ACL un list for a user
     */
    public function getGPrivileges()
    {
        if ($this->gprivileges === false) {
            $this->GetPrivileges(true, false);
        }
        return $this->gprivileges;
    }

    /**
     * Get all ACL for a given application
     */
    public function getAllAcls()
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Acl::class);

        $res = $query->Query();
        $aclList = array();
        if ($query->nb > 0) {
            foreach ($res as $v) {
                $aclList[] = $v->id;
            }
        }
        return $aclList;
    }

    /**
     * Returns the resulting ACL for a given (user, application), computing
     * ACL value if they are empty.
     * @param int $uid user id
     * @return array
     * @throws \Dcp\Db\Exception
     */
    public function getComputedPrivileges($uid)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Permission::class);
        $query->basic_elem->sup_where = array(
            "id_user = '" . $uid . "'",
            "computed = TRUE"
        );
        $computedAcl = array();
        $list = $query->Query();
        if ($query->nb > 0) {
            foreach ($list as $v) {
                $computedAcl[abs($v->id_acl)] = $v->id_acl;
            }
        }
        $allAclList = $this->getAllAcls();
        foreach ($allAclList as $acl) {
            if (!array_key_exists($acl, $computedAcl)) {
                $computedAcl[abs($acl)] = $this->computePerm($uid, abs($acl));
            }
        }

        return array_values($computedAcl);
    }

    /**
     * Return the ACL value for a given (user, app, acl), computing it if it's not
     * already computed, and storing the results.
     * @param $uid
     * @param $acl
     * @return mixed
     * @throws \Dcp\Db\Exception
     */
    protected function computePerm($uid, $acl)
    {
        $sql = (sprintf("SELECT computePerm(%d, %d)", $uid, abs($acl)));
        \Anakeen\Core\DbManager::query($sql, $cperm, true, true);
        return $cperm;
    }

    /**
     * return ACL list for a user
     * @param bool $force
     * @param bool $computed
     * @return array
     * @throws \Dcp\Core\Exception
     * @throws \Dcp\Db\Exception
     */
    public function getPrivileges($force = false, $computed = true)
    {
        if (!$force) {
            $privileges = "";
            if ($computed) {
                $privileges = $this->GetComputedPrivileges($this->id_user);
                if (count($privileges) <= 0) {
                    $privileges = "";
                }
            }
            if ($privileges !== "") {
                $this->privileges = $privileges;
                return $this->privileges;
            }
        }
        $this->privileges = array();
        $this->upprivileges = array();
        $this->unprivileges = array();
        $this->gprivileges = array();
        // add groups privilege
        $ugroup = new Group($this->dbaccess, $this->id_user);

        foreach ($ugroup->groups as $gid) {
            $gperm = new permission($this->dbaccess, array(
                $gid,
                false
            ), '', 0, $computed);
            // add group
            foreach ($gperm->privileges as $gacl) {
                if (!in_array($gacl, $this->privileges)) {
                    $this->gprivileges[] = $gacl;
                    $this->privileges[] = $gacl;
                }
            }
        }

        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Permission::class);
        $query->basic_elem->sup_where = array(
            "id_user='{$this->id_user}'",
            (!$computed) ? "( computed = FALSE OR computed IS NULL )" : "true"
        );
        $list = $query->Query();
        if ($query->nb > 0) {
            foreach ($list as $v) {
                if ($v->id_acl > 0) {
                    // add privilege
                    $this->upprivileges[] = $v->id_acl;
                    if (!in_array($v->id_acl, $this->privileges)) {
                        $this->privileges[] = $v->id_acl;
                    }
                } else {
                    // suppress privilege
                    $this->unprivileges[] = -($v->id_acl);

                    $nk = array_search(-($v->id_acl), $this->privileges, false);
                    if (is_integer($nk)) {
                        unset($this->privileges[$nk]);
                    }
                }
            }
        }

        return ($this->privileges);
    }

    /**
     * return true if user has this privilege
     * @param string $idacl  acl id
     * @param bool   $strict set to true to not use substitute user account property
     * @return bool
     */
    public function hasPrivilege($idacl, $strict = false)
    {
        $grant = (($this->id_user == 1) || // admin user
            (in_array($idacl, $this->privileges)));
        if ($grant) {
            return true;
        }
        if ($strict) {
            return $grant;
        }
        return $this->substituteHasPrivilege($idacl);
    }

    /**
     * return true if incumbent user has this privilege
     * @param string $idacl acl id
     * @return bool
     */
    public function substituteHasPrivilege($idacl)
    {
        $u = new \Anakeen\Core\Account($this->dbaccess, $this->id_user);
        $incumbents = $u->getIncumbents();
        foreach ($incumbents as $aIncumbent) {
            $p = new Permission($this->dbaccess, array(
                $aIncumbent
            ));
            $grant = $p->hasPrivilege($idacl, true);
            if ($grant) {
                return true;
            }
        }
        return false;
    }


    /**
     * delete permissions
     * @param int $id_user
     * @param string $id_acl
     * @param bool $computed
     * @return bool|string
     */
    public function deletePermission($id_user = null, $id_acl = null, $computed = null)
    {
        $sqlCond = array();
        if ($id_user != null) {
            $sqlCond[] = sprintf("( id_user = %d )", pg_escape_string($id_user));
        }

        if ($id_acl != null) {
            $sqlCond[] = sprintf("( abs(id_acl) = abs(%d) )", pg_escape_string($id_acl));
        }
        if ($computed != null) {
            if ($computed = true) {
                $sqlCond[] = "( computed = TRUE )";
            } else {
                $sqlCond[] = "( computed = FALSE OR computed IS NULL )";
            }
        }

        if (count($sqlCond) > 0) {
            return $this->query(sprintf("DELETE FROM permission WHERE ( %s )", join(" AND ", $sqlCond)));
        }

        return false;
    }
}
