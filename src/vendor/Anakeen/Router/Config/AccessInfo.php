<?php
namespace Anakeen\Router\Config;

/**
 * Class AccessInfo
 *
 * Configuration access data for routes
 *
 * @package Anakeen\Router
 */
class AccessInfo
{
    public $name;
    public $description;
    public $category;
    public $applicationContext = "CORE";
    public $configFile = "";

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
        $acl = new \Acl();
        $acl->set($this->name);

        if (!$acl->isAffected()) {
            $this->addAccess();
        } else {
            $this->updateApplication($acl);
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
        $acl->grant_level = 1;
        $acl->group_default = 'N';

        $acl->add();
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
