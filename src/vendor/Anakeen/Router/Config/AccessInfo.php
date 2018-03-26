<?php

namespace Anakeen\Router;

use Anakeen\Core\DbManager;

/**
 * Class AccessInfo
 *
 * Configuration access data for an application
 *
 * @see     \Application
 * @package Anakeen\Router
 */
class AccessInfo
{
    public $name;
    public $description;
    public $category;
    public $applicationContext = "CORE";

    /**
     * @var \Application
     */
    protected $application;
    /**
     * @var \Acl
     */
    protected $acl;
    protected $idApplication;

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
     * Record application to database
     *
     * @throws Exception
     * @throws \Dcp\Db\Exception
     */
    public function record()
    {

        DbManager::query(sprintf("select id from application where name='%s'", pg_escape_string($this->applicationContext)), $this->idApplication, true, true);
        if (!$this->idApplication) {
            throw new Exception("ROUTES0127", $this->applicationContext);
        }

        $acl = new \Acl();
        $acl->set($this->name, $this->idApplication);


        if (!$acl->isAffected()) {
            $this->addAccess();
        } else {
            $this->updateApplication($acl);
        }
    }

    /**
     * Record new application to database
     *
     * @param bool $fullInit if false not add parameters
     *
     * @throws Exception
     */
    public function addAccess()
    {
        $acl = new \Acl();
        $acl->name = $this->name;
        $acl->description = $this->description;
        $acl->grant_level = 1;
        $acl->group_default = 'N';
        $acl->id_application=$this->idApplication;

        $acl->add();
    }

    /**
     * Update application to database
     *
     * @param \Application $app
     *
     * @throws Exception
     */
    protected function updateApplication(\Acl $acl)
    {
        $acl->description = $this->description;
        $acl->modify();
    }

}
