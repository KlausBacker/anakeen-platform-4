<?php
/**
 * Access Control for application
 *
 */

use \Anakeen\LogManager;

class Acl extends DbObj
{
    public $fields = array(
        "id",
        "name",
        "description",
        "group_default"
    );

    public $id_fields = array(
        "id"
    );
    public $id;
    public $name;
    public $description;
    public $group_default;
    public $dbtable = "acl";

    public $sqlcreate = '
create table acl (id int not null,
                  name text not null,
                  description text,
                  group_default char);
create unique index acl_idx1 on acl(id);
create unique index acl_idx3 on acl(name);
create sequence SEQ_ID_ACL;
                 ';

    public function set($name)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Acl::class);
        $query->basic_elem->sup_where = array(
            "name='$name'"
        );
        $query->Query(0, 0, "TABLE");

        if ($query->nb > 0) {
            $this->Affect($query->list[0]);
        } else {
            return false;
        }
        return true;
    }


    public function preInsert()
    {
        if ($this->Exists($this->name)) {
            return "Acl {$this->name} already exists...";
        }
        $this->query("select nextval ('seq_id_acl')");
        $arr = $this->fetchArray(0);
        $this->id = $arr["nextval"];
        return '';
    }

    public function preUpdate()
    {
        if ($this->dbid == -1) {
            return false;
        }
        return '';
    }

    public function exists($name)
    {
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \Acl::class);
        $query->basic_elem->sup_where = array(
            "name='$name'"
        );
        $query->Query(0, 0, "TABLE");
        return ($query->nb > 0);
    }



    public function init($app, $app_acl, $update = false)
    {
        if (sizeof($app_acl) == 0) {
            LogManager::debug("No acl available");
            return ("");
        }


        // read init file
        $default_user_acl = array(); // default acl ids
        $default_acl = false; // to update default acl id
        $smalestgrant = null;
        foreach ($app_acl as $k => $tab) {
            $acl = new Acl($this->dbaccess);
            if ($acl->Exists($tab["name"])) {
                $acl->Set($tab["name"]);
            }
            $acl->name = $tab["name"];
            if (isset($tab["description"])) {
                $acl->description = $tab["description"];
            }

            if ((isset($tab["group_default"])) && ($tab["group_default"] == "Y")) {
                $acl->group_default = "Y";
                $default_acl = true;
            } else {
                $acl->group_default = "N";
            }

            if ($acl->exists($acl->name)) {
                LogManager::info("Acl Modify : {$acl->name}, {$acl->description}");
                $acl->Modify();
            } else {
                LogManager::info("Acl Add : {$acl->name}, {$acl->description}");
                $acl->add();
            }
            if (isset($tab["admin"]) && $tab["admin"]) {
                $permission = new Permission($this->dbaccess);
                $permission->id_user = 1;
                $permission->id_application = $app->id;
                $permission->id_acl = $acl->id;
                if ($permission->Exists($permission->id_user, $permission->id_acl)) {
                    LogManager::info("Modify admin permission : {$acl->name}");
                    $permission->Modify();
                } else {
                    LogManager::info("Create admin permission : {$acl->name}");
                    $permission->add();
                }
            }
            if ($default_acl) {
                $default_user_acl[] = $acl->id;
                $default_acl = false;
            }
        }

        // create default permission
        foreach ($default_user_acl as $ka => $aclid) {
            // set the default user access
            $defaultacl = new Acl($this->dbaccess, $aclid);
            $defaultacl->group_default = "Y";
            $defaultacl->Modify();

            if (!$update) {
                // set default access to 'all' group only
                $permission = new Permission($this->dbaccess);
                $permission->id_user = 2;
                $permission->id_application = $app->id;
                $permission->id_acl = $aclid;
                if (!$permission->exists($permission->id_user, $permission->id_acl)) {
                    $permission->add();
                }
            }
        }
        return '';
    }



}
