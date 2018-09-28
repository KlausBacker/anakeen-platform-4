<?php

namespace Anakeen\Router\Config;

use Anakeen\Core\Account;
use Anakeen\Router\Exception;

/**
 * Class AccessInfo
 *
 * Configuration access data for routes
 *
 * @package Anakeen\Router
 */
class AccessInfo
{
    const ROUTEACCESSFIELD = "_routeAccesses_";
    public $name;
    public $description;
    public $category;
    public $configFile = "";
    public $routeAccess = [];
    public $aclid; // Complete acl reference

    /**
     * @var \Acl
     */
    protected $acl;

    public function __construct($data = null)
    {
        if ($data) {
            $vars = get_object_vars($data);

            foreach ($vars as $k => $v) {
                $this->$k = $v;
            }
        }
    }

    /**
     * Record context accesses
     *
     */
    public function record()
    {
        if ($this->name !== self::ROUTEACCESSFIELD) {
            $acl = new \Acl();
            $acl->set($this->name);

            if (!$acl->isAffected()) {
                $this->addAccess();
            } else {
                $this->updateApplication($acl);
            }
        } else {
            foreach ($this->routeAccess as $routeAccess) {
                $this->addPermission($routeAccess);
            }
        }
    }

    /**
     * Record new application to database
     *
     */
    public function addAccess()
    {
        $acl = new \Acl();
        $acl->name = $this->name;
        $acl->description = $this->description;
        $acl->group_default = 'N';

        $acl->add();
    }

    protected function addPermission($routeAccess)
    {
        $login = $routeAccess->account;
        $acl = new \Acl();
        $acl->set($routeAccess->aclid);

        if (!$acl->isAffected()) {
            throw new Exception("ROUTES0141", $routeAccess->aclid);
        }
        $u = new Account();
        $u->setLoginName($login);
        if (!$u->isAffected()) {
            throw new Exception("ROUTES0140", $login);
        }
        $err = "";
        $permission = new \Permission();
        if (isset($routeAccess->policy) && $routeAccess->policy === "delete") {
            if ($permission->exists($u->id, $acl->id)) {
                $err = $permission->deletePermission($u->id, $acl->id);
            }
        } else {
            if (!$permission->exists($u->id, $acl->id)) {
                $permission->id_user = $u->id;
                $permission->id_acl = $acl->id;
                $err = $permission->add();
            }
        }
        if ($err) {
            throw new Exception($err);
        }
    }

    /**
     * Update application to database
     * @param \Acl $acl
     */
    protected function updateApplication(\Acl $acl)
    {
        $acl->description = $this->description;
        $acl->modify();
    }
}
