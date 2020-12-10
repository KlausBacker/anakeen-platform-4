<?php
/** @noinspection PhpIllegalPsrClassPathInspection */

/**
 * Permission to execute routes
 */
class Permission extends \Anakeen\Core\Internal\DbObj
{
    public $fields = array(
        "id_user",
        "id_acl"
    );

    public $id_fields = array(
        "id_user",
    );

    public $dbtable = "permission";
    protected $privileges = array(); // privileges array for a user (including group) in an application
    public $sqlcreate = '
create table permission (id_user int not null,
                         id_acl int not null);
create index permission_idx1 on permission(id_user);
create index permission_idx3 on permission(id_acl);
                 ';

    public $id_user;
    public $id_acl;


    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        if ($id && $id[0]) {
            parent::__construct($dbaccess, $id, $res, $dbid);
        } else {
            parent::__construct($dbaccess, '', $res, $dbid);
        }
        if (!$this->isAffected()) {
            if (is_array($id) && $id[0]) {
                $this->Affect(array(
                    "id_user" => $id[0]
                ));
            }
        }
    }


    public function preInsert()
    {
        // no duplicate items
        if ($this->exists($this->id_user, $this->id_acl)) {
            return "Permission ({$this->id_user},{$this->id_acl}) already exists...";
        }

        return "";
    }


    public function exists($userid, $aclid = 0)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Permission::class);
        $query->basic_elem->sup_where = [sprintf("id_user=%d", $userid)];
        if ($aclid != 0) {
            $query->addQuery(sprintf("(id_acl=%d)", $aclid));
        }
        $query->query(0, 0, "TABLE");

        return ($query->nb > 0);
    }


    /**
     * return true if user has this privilege
     * @param string $idacl acl id
     * @param bool $strict set to true to not use substitute user account property
     * @return bool
     */
    public function hasPrivilege($idacl, $strict = false)
    {
        if ($this->id_user == 1) {
            // admin user can do anything
            return true;
        }

        $grant = in_array($idacl, $this->getUserPermission());
        if ($grant) {
            return true;
        }
        if ($strict) {
            return false;
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
     * @param int|null $id_user
     * @param string|null $id_acl
     * @return bool|string
     */
    public function deletePermission($id_user = null, $id_acl = null)
    {
        $sqlCond = array();
        if ($id_user != null) {
            $sqlCond[] = sprintf("( id_user = %d )", pg_escape_string($id_user));
        }

        if ($id_acl != null) {
            $sqlCond[] = sprintf("( abs(id_acl) = abs(%d) )", pg_escape_string($id_acl));
        }

        if (count($sqlCond) > 0) {
            return $this->query(sprintf("DELETE FROM permission WHERE ( %s )", join(" AND ", $sqlCond)));
        }

        return false;
    }

    protected function getUserPermission()
    {
        if (!$this->id_user) {
            throw new \Anakeen\Exception("ACCS0008");
        }

        if (!isset($this->privileges[$this->id_user])) {
            \Anakeen\Core\DbManager::query(
                sprintf(
                    "select distinct(permission.id_acl) from permission,users where  (users.memberof  @> ARRAY[permission.id_user] or permission.id_user = %d)  and users.id=%d",
                    $this->id_user,
                    $this->id_user
                ),
                $aclIds,
                true,
                false
            );

            $this->privileges[$this->id_user] = $aclIds;
        }

        return  $this->privileges[$this->id_user];
    }
}
